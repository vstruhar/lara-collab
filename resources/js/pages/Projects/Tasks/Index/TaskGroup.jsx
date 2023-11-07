import { Draggable } from "@hello-pangea/dnd";
import { ActionIcon, Group, Text, rem } from "@mantine/core";
import { IconGripVertical, IconPlus } from "@tabler/icons-react";
import classes from "../css/Index.module.css";
import Task from "./Task";
import TaskGroupActions from "./TaskGroupActions";

export default function TaskGroup({ group, tasks, ...props }) {
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
          <div>
            {tasks.map((task) => (
              <Task key={task.id} task={task} />
            ))}
          </div>
        </div>
      )}
    </Draggable>
  );
}
