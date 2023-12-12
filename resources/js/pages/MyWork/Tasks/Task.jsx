import { Label } from "@/components/Label";
import TaskGroupLabel from "@/components/TaskGroupLabel";
import { diffForHumans } from "@/utils/datetime";
import { redirectTo } from "@/utils/route";
import { isOverdue } from "@/utils/task";
import { shortName } from "@/utils/user";
import { Link } from "@inertiajs/react";
import { Flex, Group, Pill, Text, Tooltip, rem } from "@mantine/core";
import classes from "./css/Task.module.css";

export default function Task({ task }) {
  return (
    <Flex
      className={`${classes.task} ${task.completed_at !== null && classes.completed}`}
      wrap="nowrap"
    >
      <Group gap="sm" wrap="nowrap">
        <Tooltip label="Task group" openDelay={1000} withArrow>
          <TaskGroupLabel size="sm">{task.task_group.name}</TaskGroupLabel>
        </Tooltip>
        {task.assigned_to_user && (
          <Link href={route("users.edit", task.assigned_to_user.id)}>
            <Tooltip label={task.assigned_to_user.name} openDelay={1000} withArrow>
              <Pill size="sm" className={classes.user}>
                {shortName(task.assigned_to_user.name)}
              </Pill>
            </Tooltip>
          </Link>
        )}

        <Tooltip
          disabled={!isOverdue(task)}
          label={`${diffForHumans(task.due_on, true)} overdue`}
          openDelay={1000}
          withArrow
        >
          <Text
            className={classes.name}
            size="sm"
            fw={500}
            truncate="end"
            c={isOverdue(task) && task.completed_at === null ? "red" : ""}
            onClick={() => redirectTo("projects.tasks.open", [task.project_id, task.id])}
          >
            #{task.number + ": " + task.name}
          </Text>
        </Tooltip>

        <Group wrap="wrap" style={{ rowGap: rem(3), columnGap: rem(12) }}>
          {task.labels.map((label) => (
            <Label key={label.id} name={label.name} color={label.color} />
          ))}
        </Group>
      </Group>
    </Flex>
  );
}
