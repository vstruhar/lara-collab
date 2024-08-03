import ArchivedFilterButton from "@/components/ArchivedFilterButton";
import ClearFiltersButton from "@/components/ClearFiltersButton";
import SearchInput from "@/components/SearchInput";
import useTaskDrawerStore from "@/hooks/store/useTaskDrawerStore";
import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import usePreferences from "@/hooks/usePreferences";
import { reloadWithQuery } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import { ActionIcon, Button, Grid, Group, Text, Title, Tooltip } from "@mantine/core";
import {
  IconFilter,
  IconFilterCog,
  IconLayoutKanban,
  IconLayoutList,
  IconPlus,
} from "@tabler/icons-react";

export default function Header() {
  const { project } = usePage().props;

  const { tasksView, setTasksView } = usePreferences();
  const { openDrawer } = useTaskFiltersStore();
  const search = (search) => reloadWithQuery({ search });

  const { openCreateTask } = useTaskDrawerStore();
  const { hasUrlParams } = useTaskFiltersStore();
  const usingFilters = hasUrlParams(["archived"]);

  return (
    <Grid justify="space-between" align="end">
      <Grid.Col span="content">
        <Group mb="lg">
          <Title order={1}>
            {project.name}
            {project.archived_at && <Text size="2rem" fw={500} c="red.8" ml="md" span>(archived)</Text>}
          </Title>
        </Group>
        <Group>
          <SearchInput placeholder="Search tasks" search={search} mr="md" />

          <ActionIcon.Group>
            {tasksView === "kanban" && (
              <Tooltip label="Filters" openDelay={500} withArrow>
                <ActionIcon variant="filled" size="lg" onClick={() => openDrawer()}>
                  {usingFilters ? (
                    <IconFilterCog style={{ width: "60%", height: "60%" }} stroke={1.5} />
                  ) : (
                    <IconFilter style={{ width: "60%", height: "60%" }} stroke={1.5} />
                  )}
                </ActionIcon>
              </Tooltip>
            )}
            {usingFilters && <ClearFiltersButton />}
          </ActionIcon.Group>

          <ArchivedFilterButton />
        </Group>
      </Grid.Col>
      <Grid.Col span="content">
        <Group>
          <Group mr="sm" gap={10}>
            <ActionIcon.Group>
              <ActionIcon
                size="lg"
                variant={tasksView === "list" ? "filled" : "default"}
                onClick={() => setTasksView("list")}
              >
                <Tooltip label="List view" openDelay={250} withArrow>
                  <IconLayoutList style={{ width: "40%", height: "40%" }} />
                </Tooltip>
              </ActionIcon>
              <ActionIcon
                size="lg"
                variant={tasksView === "kanban" ? "filled" : "default"}
                onClick={() => setTasksView("kanban")}
              >
                <Tooltip label="Kanban view" openDelay={250} withArrow>
                  <IconLayoutKanban style={{ width: "45%", height: "45%" }} />
                </Tooltip>
              </ActionIcon>
            </ActionIcon.Group>
          </Group>

          {can("create task") && (
            <Button
              leftSection={<IconPlus size={14} />}
              radius="xl"
              onClick={() => openCreateTask()}
            >
              Add task
            </Button>
          )}
        </Group>
      </Grid.Col>
    </Grid>
  );
}
