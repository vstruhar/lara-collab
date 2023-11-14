import useTasksStore from "@/hooks/store/useTasksStore";
import { isOverdue } from "@/utils/task";
import { shortName } from "@/utils/user";
import { Draggable } from "@hello-pangea/dnd";
import { Link } from "@inertiajs/react";
import { Checkbox, Flex, Group, Pill, Text, Tooltip, rem } from "@mantine/core";
import { IconGripVertical } from "@tabler/icons-react";
import { Label } from "./../../../../components/Label";
import classes from "./css/Task.module.css";

export default function Task({ task, index }) {
  const { complete } = useTasksStore();

  const toggleCompleted = (state) => {
    complete(task, state);
  };

  return (
    <Draggable draggableId={"task-" + task.id} index={index}>
      {(provided, snapshot) => (
        <Flex
          {...provided.draggableProps}
          ref={provided.innerRef}
          className={`${classes.task} ${
            snapshot.isDragging && classes.itemDragging
          } ${task.completed_at !== null && classes.completed}`}
        >
          <Group gap="sm">
            <div {...provided.dragHandleProps}>
              <IconGripVertical
                style={{
                  width: rem(18),
                  height: rem(18),
                  display: can("reorder task") ? "inline" : "none",
                }}
                stroke={1.5}
                className={classes.dragHandle}
              />
            </div>

            <Checkbox
              size="sm"
              radius="xl"
              color="green"
              checked={task.completed_at !== null}
              onChange={(e) => toggleCompleted(e.currentTarget.checked)}
              className={
                can("complete task")
                  ? classes.checkbox
                  : classes.disabledCheckbox
              }
            />
            {task.assigned_to_user && (
              <Link href={route("users.edit", task.assigned_to_user.id)}>
                <Tooltip
                  label={task.assigned_to_user.name}
                  openDelay={1000}
                  withArrow
                >
                  <Pill size="sm" className={classes.user}>
                    {shortName(task.assigned_to_user.name)}
                  </Pill>
                </Tooltip>
              </Link>
            )}
            <Text
              key={task.id}
              size="sm"
              fw={500}
              c={isOverdue(task) && task.completed_at === null ? "red.7" : ""}
            >
              #{task.number + ": " + task.name}
            </Text>

            <Group gap={12} ml={8}>
              {task.labels.map((label) => (
                <Label key={label.id} name={label.name} color={label.color} />
              ))}
            </Group>
          </Group>
        </Flex>
      )}
    </Draggable>
  );
}
