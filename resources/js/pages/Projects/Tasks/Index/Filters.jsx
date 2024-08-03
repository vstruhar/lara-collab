import useTaskGroupsStore from "@/hooks/store/useTaskGroupsStore";
import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import { usePage } from "@inertiajs/react";
import { ColorSwatch, Stack, Text } from "@mantine/core";
import FilterButton from "./Filters/FilterButton";

export default function Filters() {
  const { usersWithAccessToProject, labels } = usePage().props;

  const { groups } = useTaskGroupsStore();
  const { filters, toggleArrayFilter, toggleObjectFilter, toggleValueFilter } =
    useTaskFiltersStore();

  return (
    <>
      <Stack justify="flex-start" gap={24}>
        {groups.length > 0 && (
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
        )}

        {usersWithAccessToProject.length > 0 && (
          <div>
            <Text fz="xs" fw={700} tt="uppercase" mb="sm">
              Assignees
            </Text>
            <Stack justify="flex-start" gap={6}>
              {usersWithAccessToProject.map((item) => (
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
        )}

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
            Status
          </Text>
          <Stack justify="flex-start" gap={6}>
            <FilterButton
              selected={filters.status === "completed"}
              onClick={() => toggleValueFilter("status", "completed")}
            >
              Completed
            </FilterButton>
          </Stack>
        </div>

        {labels.length > 0 && (
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
        )}
      </Stack>
    </>
  );
}
