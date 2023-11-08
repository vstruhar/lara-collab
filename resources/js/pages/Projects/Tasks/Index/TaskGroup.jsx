import { Draggable } from "@hello-pangea/dnd";
import { ActionIcon, Group, Text, rem } from "@mantine/core";
import { IconGripVertical, IconPlus } from "@tabler/icons-react";
import { useState } from "react";
import Task from "./Task";
import TaskGroupActions from "./TaskGroupActions";
import classes from "./css/TaskGroup.module.css";

export default function TaskGroup({ group, tasks, ...props }) {
  const [stateTasks, setTasks] = useState(tasks);

  const completed = stateTasks.filter((i) => i.completed_at !== null);
  const notCompleted = stateTasks.filter((i) => i.completed_at === null);

  const completeTask = (task, state) => {
    const item = stateTasks.find((i) => i.id === task.id);

    item.completed_at = state ? true : null;

    setTasks([...stateTasks]);
  };

  return (
    <Draggable draggableId={group.id.toString()} {...props}>
      {(provided, snapshot) => (
        <div
          className={`${classes.row} ${
            snapshot.isDragging && classes.itemDragging
          }`}
          ref={provided.innerRef}
          {...provided.draggableProps}
        >
          <div className={classes.group}>
            <Group>
              <div {...provided.dragHandleProps} className={classes.dragHandle}>
                <IconGripVertical
                  style={{ width: rem(18), height: rem(18) }}
                  stroke={1.5}
                />
              </div>
              <Text size="xl" fw={700}>
                {group.name}
              </Text>
              <TaskGroupActions group={group} className={classes.actions} />
            </Group>
            <ActionIcon variant="filled" size="md" radius="xl" mr={-10}>
              <IconPlus
                style={{ width: rem(18), height: rem(18) }}
                stroke={2}
              />
            </ActionIcon>
          </div>
          {notCompleted.length > 0 &&
            notCompleted.map((task) => (
              <Task key={task.id} task={task} completeTask={completeTask} />
            ))}
          {completed.length > 0 &&
            completed.map((task) => (
              <Task
                key={task.id}
                task={task}
                completeTask={completeTask}
                opacity={0.4}
              />
            ))}
        </div>
      )}
    </Draggable>
  );
}
