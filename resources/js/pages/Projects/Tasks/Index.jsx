import Layout from "@/layouts/MainLayout";
import { DragDropContext, Droppable } from "@hello-pangea/dnd";
import { usePage } from "@inertiajs/react";
import { Grid } from "@mantine/core";
import { useDidUpdate, useListState } from "@mantine/hooks";
import axios from "axios";
import Header from "./Index/Header";
import TaskGroup from "./Index/TaskGroup";

let currentProject = null;

const TasksIndex = () => {
  const { project, taskGroups, groupedTasks } = usePage().props;
  currentProject = project;

  const [groups, handlers] = useListState(taskGroups);

  useDidUpdate(() => {
    axios.post(route("projects.task-groups.reorder", project.id), {
      ids: groups.map((i) => i.id),
    });
  }, [groups, project.id]);

  return (
    <>
      <Header />

      <Grid columns={12} gutter="lg" mt="xl">
        <Grid.Col span="auto">
          <DragDropContext
            onDragEnd={({ source, destination }) =>
              handlers.reorder({
                from: source.index,
                to: destination?.index || 0,
              })
            }
          >
            <Droppable droppableId="dnd-group-list" direction="vertical">
              {(provided) => (
                <div {...provided.droppableProps} ref={provided.innerRef}>
                  {groups.map((group, index) => (
                    <TaskGroup
                      key={group.id}
                      index={index}
                      group={group}
                      tasks={groupedTasks[group.id] || []}
                    />
                  ))}
                  {provided.placeholder}
                </div>
              )}
            </Droppable>
          </DragDropContext>
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
