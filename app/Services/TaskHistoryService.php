<?php

namespace App\Services;

use App\Models\Label;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\TaskPriority;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LaravelArchivable\Archivable;
use OwenIt\Auditing\Models\Audit;

class TaskHistoryService
{
    private const PER_PAGE = 30;

    private const MERGE_MINUTES = 10;

    private const PRICE_FIELDS = [
        'fixed_price',
        'pricing_type',
        'billable',
    ];

    private const RELATIONS = [
        'labels' => 'labels',
        'subscribedUsers' => 'subscribed_users',
    ];

    private const ATTRIBUTE_FIELDS = [
        'name',
        'description',
        'group_id',
        'assigned_to_user_id',
        'due_on',
        'estimation',
        'priority_id',
        'pricing_type',
        'fixed_price',
        'billable',
        'hidden_from_clients',
        'completed_at',
        'archived_at',
    ];

    public function forTask(Task $task, User $viewer, int $page): array
    {
        $audits = $task->audits()
            ->with(['user' => function ($query): void {
                $query->withArchived()->select('id', 'name', 'avatar');
            }])
            ->latest()
            ->orderByDesc('id')
            ->simplePaginate(perPage: self::PER_PAGE, page: $page);

        $entries = [];

        foreach ($audits as $audit) {
            foreach ($this->entriesForAudit($audit) as $entry) {
                if ($this->viewerCanSee($viewer, $entry['field'])) {
                    $entries[] = $entry;
                }
            }
        }

        return [
            'data' => $this->resolveNames($this->mergeEntries($entries)),
            'has_more' => $audits->hasMorePages(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entriesForAudit(Audit $audit): array
    {
        if ($audit->event === 'created') {
            return [[
                'id' => $audit->getKey().'-created',
                'type' => 'created',
                'field' => null,
                'old' => null,
                'new' => null,
                'user' => $this->actor($audit),
                'created_at' => $this->timestamp($audit),
            ]];
        }

        $oldValues = $audit->old_values ?? [];
        $newValues = $audit->new_values ?? [];
        $entries = [];

        foreach (self::RELATIONS as $auditKey => $field) {
            if (! array_key_exists($auditKey, $oldValues) && ! array_key_exists($auditKey, $newValues)) {
                continue;
            }

            $entries[] = $this->entry(
                $audit,
                $field,
                $this->idsFromAuditValue($oldValues[$auditKey] ?? []),
                $this->idsFromAuditValue($newValues[$auditKey] ?? []),
            );
        }

        foreach (self::ATTRIBUTE_FIELDS as $field) {
            if (! array_key_exists($field, $oldValues) && ! array_key_exists($field, $newValues)) {
                continue;
            }

            $entries[] = $this->entry(
                $audit,
                $field,
                $this->normalizeValue($field, $oldValues[$field] ?? null),
                $this->normalizeValue($field, $newValues[$field] ?? null),
            );
        }

        return $entries;
    }

    /**
     * @return array<string, mixed>
     */
    private function entry(Audit $audit, string $field, mixed $old, mixed $new): array
    {
        return $this->applyFieldType([
            'id' => $audit->getKey().'-'.$field,
            'type' => 'updated',
            'field' => $field,
            'old' => $old,
            'new' => $new,
            'user' => $this->actor($audit),
            'created_at' => $this->timestamp($audit),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>
     */
    private function mergeEntries(array $entries): array
    {
        $merged = [];

        foreach ($entries as $entry) {
            $index = array_key_last($merged);

            if ($index === null) {
                if (! $this->isNoOp($entry)) {
                    $merged[] = $entry;
                }

                continue;
            }

            if (! $this->canMerge($merged[$index], $entry)) {
                if (! $this->isNoOp($entry)) {
                    $merged[] = $entry;
                }

                continue;
            }

            $merged[$index] = $this->mergePair($merged[$index], $entry);

            if ($this->isNoOp($merged[$index])) {
                array_pop($merged);
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $newer
     * @param  array<string, mixed>  $older
     */
    private function canMerge(array $newer, array $older): bool
    {
        if ($newer['field'] === null || $newer['field'] !== $older['field']) {
            return false;
        }

        // Completing, reopening, archiving, and restoring are discrete actions.
        if (in_array($newer['field'], ['completed_at', 'archived_at'], true)) {
            return false;
        }

        if (($newer['user']['id'] ?? null) !== ($older['user']['id'] ?? null)) {
            return false;
        }

        $minutes = abs(Carbon::parse($newer['created_at'])->diffInMinutes(Carbon::parse($older['created_at'])));

        return $minutes <= self::MERGE_MINUTES;
    }

    /**
     * @param  array<string, mixed>  $newer
     * @param  array<string, mixed>  $older
     * @return array<string, mixed>
     */
    private function mergePair(array $newer, array $older): array
    {
        if (in_array($newer['field'], ['labels', 'subscribed_users'], true)) {
            [$removed, $added] = $this->mergeRelationIds($newer, $older);
            $newer['old'] = $removed;
            $newer['new'] = $added;
        } else {
            $newer['old'] = $older['old'];
        }

        return $this->applyFieldType($newer);
    }

    /**
     * Replay the older change first, then the newer one, so adds and removes cancel out.
     *
     * @param  array<string, mixed>  $newer
     * @param  array<string, mixed>  $older
     * @return array{0: array<int, int>, 1: array<int, int>}
     */
    private function mergeRelationIds(array $newer, array $older): array
    {
        $added = array_values(array_unique([
            ...array_values(array_diff($older['new'], $newer['old'])),
            ...array_values(array_diff($newer['new'], $older['old'])),
        ]));
        $removed = array_values(array_unique([
            ...array_values(array_diff($older['old'], $newer['new'])),
            ...array_values(array_diff($newer['old'], $older['new'])),
        ]));
        $overlap = array_intersect($added, $removed);

        return [
            array_values(array_diff($removed, $overlap)),
            array_values(array_diff($added, $overlap)),
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function isNoOp(array $entry): bool
    {
        if ($entry['field'] === null) {
            return false;
        }

        if (in_array($entry['field'], ['labels', 'subscribed_users'], true)) {
            return $entry['old'] === [] && $entry['new'] === [];
        }

        return $entry['old'] === $entry['new'];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function applyFieldType(array $entry): array
    {
        $entry['type'] = match ($entry['field']) {
            'completed_at' => $entry['new'] ? 'completed' : 'reopened',
            'archived_at' => $entry['new'] ? 'archived' : 'restored',
            default => $entry['type'],
        };

        return $entry;
    }

    private function viewerCanSee(User $viewer, ?string $field): bool
    {
        if ($field === null) {
            return true;
        }

        if (in_array($field, self::PRICE_FIELDS, true) && ! $viewer->hasAnyPermission(['view time logs', 'add time log'])) {
            return false;
        }

        if ($field === 'hidden_from_clients' && $viewer->hasRole('client')) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array<string, mixed>>
     */
    private function resolveNames(array $entries): array
    {
        $userIds = [];
        $groupIds = [];
        $priorityIds = [];
        $labelIds = [];

        foreach ($entries as $entry) {
            $ids = $this->referencedIds($entry);

            match ($entry['field']) {
                'assigned_to_user_id', 'subscribed_users' => $userIds = [...$userIds, ...$ids],
                'group_id' => $groupIds = [...$groupIds, ...$ids],
                'priority_id' => $priorityIds = [...$priorityIds, ...$ids],
                'labels' => $labelIds = [...$labelIds, ...$ids],
                default => null,
            };
        }

        $users = $this->keyedModels(User::class, $userIds, ['id', 'name']);
        $groups = $this->keyedModels(TaskGroup::class, $groupIds, ['id', 'name']);
        $priorities = $this->keyedModels(TaskPriority::class, $priorityIds, ['id', 'label', 'color']);
        $labels = $this->keyedModels(Label::class, $labelIds, ['id', 'name', 'color']);

        return array_map(function (array $entry) use ($users, $groups, $priorities, $labels) {
            return [
                'id' => $entry['id'],
                'type' => $entry['type'],
                'field' => $entry['field'],
                'old' => $this->presentValue($entry['field'], $entry['old'], $users, $groups, $priorities, $labels),
                'new' => $this->presentValue($entry['field'], $entry['new'], $users, $groups, $priorities, $labels),
                'user' => $entry['user'],
                'created_at' => $entry['created_at'],
            ];
        }, $entries);
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<int, int>
     */
    private function referencedIds(array $entry): array
    {
        $values = in_array($entry['field'], ['labels', 'subscribed_users'], true)
            ? [...$entry['old'], ...$entry['new']]
            : [$entry['old'], $entry['new']];

        return array_values(array_filter(
            array_map(fn ($id) => is_numeric($id) ? (int) $id : null, $values),
        ));
    }

    /**
     * @param  class-string  $model
     * @param  array<int, int>  $ids
     * @param  array<int, string>  $columns
     */
    private function keyedModels(string $model, array $ids, array $columns): Collection
    {
        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return collect();
        }

        $query = $model::query()->whereIn('id', $ids);

        if (in_array(Archivable::class, class_uses_recursive($model), true)) {
            $query->withArchived();
        }

        return $query->get($columns)->keyBy(fn ($model) => (int) $model->getKey());
    }

    private function presentValue(
        ?string $field,
        mixed $value,
        Collection $users,
        Collection $groups,
        Collection $priorities,
        Collection $labels,
    ): mixed {
        return match ($field) {
            'assigned_to_user_id' => $this->presentUser(is_int($value) ? $value : null, $users),
            'group_id' => $this->presentGroup(is_int($value) ? $value : null, $groups),
            'priority_id' => $this->presentPriority(is_int($value) ? $value : null, $priorities),
            'subscribed_users' => array_map(
                fn (int $id) => $this->presentUser($id, $users),
                $value,
            ),
            'labels' => array_map(
                fn (int $id) => $this->presentLabel($id, $labels),
                $value,
            ),
            default => $value,
        };
    }

    private function presentUser(?int $id, Collection $users): ?array
    {
        if ($id === null) {
            return null;
        }

        $user = $users->get($id);

        return [
            'id' => $id,
            'name' => $user->name ?? 'Deleted user',
        ];
    }

    private function presentGroup(?int $id, Collection $groups): ?array
    {
        if ($id === null) {
            return null;
        }

        $group = $groups->get($id);

        return [
            'id' => $id,
            'name' => $group->name ?? 'Deleted group',
        ];
    }

    private function presentPriority(?int $id, Collection $priorities): ?array
    {
        if ($id === null) {
            return null;
        }

        $priority = $priorities->get($id);

        return [
            'id' => $id,
            'label' => $priority->label ?? 'Deleted priority',
            'color' => $priority->color ?? null,
        ];
    }

    private function presentLabel(int $id, Collection $labels): array
    {
        $label = $labels->get($id);

        return [
            'id' => $id,
            'name' => $label->name ?? 'Deleted label',
            'color' => $label->color ?? null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function actor(Audit $audit): ?array
    {
        $user = $audit->user;

        if ($user instanceof User) {
            return [
                'id' => (int) $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ];
        }

        if ($audit->user_id) {
            return [
                'id' => (int) $audit->user_id,
                'name' => 'Deleted user',
                'avatar' => null,
            ];
        }

        return null;
    }

    private function timestamp(Audit $audit): string
    {
        return Carbon::parse($audit->created_at)->toISOString();
    }

    private function normalizeValue(string $field, mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match ($field) {
            'group_id', 'assigned_to_user_id', 'priority_id' => $this->normalizeId($value),
            'fixed_price' => $value === null || $value === '' ? null : (int) $value,
            'estimation' => $value === null || $value === '' ? null : (float) $value,
            'billable', 'hidden_from_clients' => $this->normalizeBoolean($value),
            'due_on' => $this->normalizeDueOn($value),
            'name', 'description' => $value === null || $value === '' ? null : (string) $value,
            default => $value === '' ? null : $value,
        };
    }

    private function normalizeId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 0 || $value === '0') {
            return null;
        }

        return (int) $value;
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value;
    }

    private function normalizeDueOn(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * @return array<int, int>
     */
    private function idsFromAuditValue(mixed $value): array
    {
        if (! is_array($value) || $value === []) {
            return [];
        }

        if (array_key_exists('id', $value)) {
            $value = [$value];
        }

        return collect($value)
            ->map(fn ($item) => is_array($item) ? (int) ($item['id'] ?? 0) : (int) $item)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
