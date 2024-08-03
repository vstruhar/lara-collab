import { EmptyResult } from "@/components/EmptyResult";
import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import useTaskGroupsStore from "@/hooks/store/useTaskGroupsStore";
import useTasksStore from "@/hooks/store/useTasksStore";
import usePreferences from "@/hooks/usePreferences";
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
import FiltersDrawer from "./Index/FiltersDrawer";
import Header from "./Index/Header";
import CreateTasksGroupModal from "./Index/Modals/CreateTasksGroupModal";
import TaskGroup from "./Index/TaskGroup";
import classes from "./css/Index.module.css";

let currentProject = null;

const TasksIndex = () => {
  const { project, taskGroups, groupedTasks, openedTask } = usePage().props;
  currentProject = project;

  const { groups, setGroups, reorderGroup } = useTaskGroupsStore();
  const { tasks, setTasks, addTask, reorderTask, moveTask } = useTasksStore();
  const { hasUrlParams } = useTaskFiltersStore();
  const { initProjectWebSocket } = useWebSockets();
  const { tasksView } = usePreferences();

  const usingFilters = hasUrlParams();

  useEffect(() => {
    setGroups(taskGroups);
    setTasks(groupedTasks);
    if (openedTask) addTask(openedTask);
  }, [taskGroups, groupedTasks]);

  useEffect(() => {
    return initProjectWebSocket(project);
  }, []);

  const onDragEnd = ({ source, destination }) => {
    if (!destination) {
      return;
    }
    if (source.droppableId.includes("tasks") && destination.droppableId.includes("tasks")) {
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

      <Grid columns={12} gutter={50} mt="xl" className={`${tasksView}-view`}>
        {!route().params.archived ? (
          <Grid.Col span={tasksView === "list" ? 9 : 12}>
            {groups.length ? (
              <>
                <DragDropContext onDragEnd={onDragEnd}>
                  <Droppable
                    droppableId="groups"
                    direction={tasksView === "list" ? "vertical" : "horizontal"}
                    type="group"
                  >
                    {(provided) => (
                      <div {...provided.droppableProps} ref={provided.innerRef}>
                        <div className={classes.viewport}>
                          {groups
                            .filter(
                              (group) =>
                                !usingFilters || (usingFilters && tasks[group.id]?.length > 0),
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
                          {!route().params.archived && can("create task group") && (
                            <Button
                              leftSection={<IconPlus size={14} />}
                              variant="transparent"
                              size="sm"
                              mt={14}
                              m={4}
                              radius="xl"
                              onClick={CreateTasksGroupModal}
                              style={{ width: "200px" }}
                            >
                              Add {tasksView === "list" ? "tasks group" : "group"}
                            </Button>
                          )}
                        </div>
                      </div>
                    )}
                  </Droppable>
                </DragDropContext>
              </>
            ) : (
              <EmptyResult title="No tasks found" subtitle="or none match your search criteria" />
            )}
          </Grid.Col>
        ) : (
          <Grid.Col span={tasksView === "list" ? 9 : 12}>
            <ArchivedItems groups={groups} tasks={tasks} />
          </Grid.Col>
        )}
        {tasksView === "list" ? (
          <Grid.Col span={3}>
            <Filters />
          </Grid.Col>
        ) : (
          <FiltersDrawer />
        )}
      </Grid>
    </>
  );
};

TasksIndex.layout = (page) => <Layout title={currentProject?.name}>{page}</Layout>;

export default TasksIndex;
