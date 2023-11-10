import useGroupsStore from "@/hooks/store/useGroupsStore";
import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import { usePage } from "@inertiajs/react";
import { ColorSwatch, Stack, Text } from "@mantine/core";
import FilterButton from "./Filters/FilterButton";

export default function Filters() {
  const { assignees, labels } = usePage().props;

  const groups = useGroupsStore((state) => state.groups);
  const filters = useTaskFiltersStore((state) => state.filters);

  const toggleArrayFilter = useTaskFiltersStore(
    (state) => state.toggleArrayFilter,
  );
  const toggleObjectFilter = useTaskFiltersStore(
    (state) => state.toggleObjectFilter,
  );

  return (
    <>
      <Stack justify="flex-start" gap={24}>
        <div>
          <Text fz="xs" fw={700} tt="uppercase" mb="sm">
            Task groups
          </Text>
          <Stack justify="flex-start" gap={6}>
            {groups.map((item) => (
              <FilterButton
                key={item.id}
                selected={filters.groups.includes(item.id)}
                onClick={() => toggleArrayFilter("groups", item.id)}
              >
                {item.name}
              </FilterButton>
            ))}
          </Stack>
        </div>

        <div>
          <Text fz="xs" fw={700} tt="uppercase" mb="sm">
            Assignees
          </Text>
          <Stack justify="flex-start" gap={6}>
            {assignees.map((item) => (
              <FilterButton
                key={item.id}
                selected={filters.assignees.includes(item.id)}
                onClick={() => toggleArrayFilter("assignees", item.id)}
              >
                {item.name}
              </FilterButton>
            ))}
          </Stack>
        </div>

        <div>
          <Text fz="xs" fw={700} tt="uppercase" mb="sm">
            Due date
          </Text>
          <Stack justify="flex-start" gap={6}>
            <FilterButton
              selected={filters.due_date.not_set === 1}
              onClick={() => toggleObjectFilter("due_date", "not_set")}
            >
              Not set
            </FilterButton>
            <FilterButton
              selected={filters.due_date.overdue === 1}
              onClick={() => toggleObjectFilter("due_date", "overdue")}
            >
              Overdue
            </FilterButton>
          </Stack>
        </div>

        <div>
          <Text fz="xs" fw={700} tt="uppercase" mb="sm">
            Labels
          </Text>
          <Stack justify="flex-start" gap={6}>
            {labels.map((item) => (
              <FilterButton
                key={item.id}
                selected={filters.labels.includes(item.id)}
                onClick={() => toggleArrayFilter("labels", item.id)}
                leftSection={<ColorSwatch color={item.color} size={18} />}
              >
                {item.name}
              </FilterButton>
            ))}
          </Stack>
        </div>
      </Stack>
    </>
  );
}