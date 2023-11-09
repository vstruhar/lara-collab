import useTasksStore from "@/hooks/useTasksStore";
import Layout from "@/layouts/MainLayout";
import { move } from "@/services/ReorderService";
import { DragDropContext, Droppable } from "@hello-pangea/dnd";
import { usePage } from "@inertiajs/react";
import { Button, Grid } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";
import { useEffect } from "react";
import Header from "./Index/Header";
import TaskGroup from "./Index/TaskGroup";

let currentProject = null;

const TasksIndex = () => {
  const { project, taskGroups, groupedTasks } = usePage().props;
  currentProject = project;

  const groups = useTasksStore((state) => state.groups);
  const setGroups = useTasksStore((state) => state.setGroups);
  const reorderGroup = useTasksStore((state) => state.reorderGroup);

  const tasks = useTasksStore((state) => state.tasks);
  const setTasks = useTasksStore((state) => state.setTasks);
  const reorderTask = useTasksStore((state) => state.reorderTask);

  useEffect(() => {
    setGroups(taskGroups);
    setTasks(groupedTasks);
  }, []);

  const onDragEnd = ({ source, destination }) => {
    if (!destination) {
      return;
    }
    if (
      source.droppableId.includes("tasks") &&
      destination.droppableId.includes("tasks")
    ) {
      // TASKS
      const newState = { ...tasks };

      const sId = +source.droppableId.split("-")[1];
      const dId = +destination.droppableId.split("-")[1];

      if (source.droppableId === destination.droppableId) {
        reorderTask(sId, source.index, destination.index);
      } else {
        // MOVE
        const result = move(tasks[sId], tasks[dId], source, destination);
        newState[sId] = result[sId];
        newState[dId] = result[dId];
      }
    } else {
      reorderGroup(source.index, destination.index);
    }
  };

  return (
    <>
      <Header />

      <Grid columns={12} gutter="lg" mt="xl">
        <Grid.Col span="auto">
          <DragDropContext onDragEnd={onDragEnd}>
            {groups.map((group, index) => (
              <Droppable
                key={group.id}
                droppableId={"group-" + group.id}
                direction="vertical"
                type="group"
              >
                {(provided) => (
                  <div {...provided.droppableProps} ref={provided.innerRef}>
                    <TaskGroup
                      key={group.id}
                      index={index}
                      group={group}
                      tasks={tasks[group.id] || []}
                    />
                    {provided.placeholder}
                  </div>
                )}
              </Droppable>
            ))}
          </DragDropContext>

          <Button
            leftSection={<IconPlus size={14} />}
            variant="transparent"
            size="sm"
            mt="md"
            radius="xl"
          >
            Add tasks group
          </Button>
        </Grid.Col>
        <Grid.Col span={3}>Filters</Grid.Col>
      </Grid>
    </>
  );
};

TasksIndex.layout = (page) => (
  <Layout title={currentProject?.name}>{page}</Layout>
);

export default TasksIndex;
