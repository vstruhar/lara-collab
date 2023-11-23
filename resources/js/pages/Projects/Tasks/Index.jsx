import { EmptyResult } from "@/components/EmptyResult";
import useGroupsStore from "@/hooks/store/useGroupsStore";
import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import useTasksStore from "@/hooks/store/useTasksStore";
import useWebSockets from "@/hooks/useWebSockets";
import Layout from "@/layouts/MainLayout";
import { DragDropContext, Droppable } from "@hello-pangea/dnd";
import { usePage } from "@inertiajs/react";
import { Button, Grid } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";
import { useEffect } from "react";
import { CreateTaskDrawer } from "./Drawers/CreateTaskDrawer";
import { EditTaskDrawer } from "./Drawers/EditTaskDrawer";
import ArchivedItems from "./Index/Archive/ArchivedItems";
import Filters from "./Index/Filters";
import Header from "./Index/Header";
import CreateTasksGroupModal from "./Index/Modals/CreateTasksGroupModal";
import TaskGroup from "./Index/TaskGroup";

let currentProject = null;

const TasksIndex = () => {
  const { project, taskGroups, groupedTasks } = usePage().props;
  currentProject = project;

  const { groups, setGroups, reorderGroup } = useGroupsStore();
  const { tasks, setTasks, reorderTask, moveTask } = useTasksStore();
  const { hasUrlParams } = useTaskFiltersStore();
  const { initProjectWebSocket } = useWebSockets();

  const usingFilters = hasUrlParams();

  useEffect(() => {
    setGroups(taskGroups);
    setTasks(groupedTasks);
  }, [taskGroups, groupedTasks]);

  useEffect(() => {
    return initProjectWebSocket(project);
  }, []);

  const onDragEnd = ({ source, destination }) => {
    if (!destination) {
      return;
    }
    if (
      source.droppableId.includes("tasks") &&
      destination.droppableId.includes("tasks")
    ) {
      if (source.droppableId === destination.droppableId) {
        reorderTask(source, destination);
      } else {
        moveTask(source, destination);
      }
    } else {
      reorderGroup(source.index, destination.index);
    }
  };

  return (
    <>
      <Header />

      {can("create task") && <CreateTaskDrawer />}
      <EditTaskDrawer />

      <Grid columns={12} gutter={50} mt="xl">
        {!route().params.archived ? (
          <Grid.Col span="auto">
            {groups.length ? (
              <>
                <DragDropContext onDragEnd={onDragEnd}>
                  <Droppable
                    droppableId="groups"
                    direction="vertical"
                    type="group"
                  >
                    {(provided) => (
                      <div {...provided.droppableProps} ref={provided.innerRef}>
                        {groups
                          .filter(
                            (group) =>
                              !usingFilters ||
                              (usingFilters && tasks[group.id]?.length > 0),
                          )
                          .map((group, index) => (
                            <TaskGroup
                              key={group.id}
                              index={index}
                              group={group}
                              tasks={tasks[group.id] || []}
                            />
                          ))}
                        {provided.placeholder}
                      </div>
                    )}
                  </Droppable>
                </DragDropContext>

                {!route().params.archived && can("create task group") && (
                  <Button
                    leftSection={<IconPlus size={14} />}
                    variant="transparent"
                    size="sm"
                    mt="md"
                    m={4}
                    radius="xl"
                    onClick={CreateTasksGroupModal}
                  >
                    Add tasks group
                  </Button>
                )}
              </>
            ) : (
              <EmptyResult
                title="No tasks found"
                subtitle="or none match your search criteria"
              />
            )}
          </Grid.Col>
        ) : (
          <Grid.Col span="auto">
            <ArchivedItems groups={groups} tasks={tasks} />
          </Grid.Col>
        )}
        <Grid.Col span={3}>
          <Filters />
        </Grid.Col>
      </Grid>
    </>
  );
};

TasksIndex.layout = (page) => (
  <Layout title={currentProject?.name}>{page}</Layout>
);

export default TasksIndex;
