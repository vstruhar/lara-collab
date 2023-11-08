import { shortName } from "@/services/UserService";
import { Link } from "@inertiajs/react";
import { Checkbox, Flex, Group, Pill, Text, rem } from "@mantine/core";
import { IconGripVertical } from "@tabler/icons-react";
import axios from "axios";
import classes from "./css/Task.module.css";

export default function Task({ task, completeTask, ...props }) {
  const toggleCompleted = (state) => {
    completeTask(task, state);

    axios
      .post(route("projects.tasks.complete", [task.project_id, task.id]), {
        completed: state,
      })
      .catch(() => alert("Failed to save task completed action"));
  };

  return (
    <Flex {...props} className={classes.task}>
      <Group gap="sm">
        <IconGripVertical
          style={{ width: rem(18), height: rem(18) }}
          stroke={1.5}
          className={classes.dragHandle}
        />

        <Checkbox
          size="sm"
          radius="xl"
          color="green"
          checked={task.completed_at !== null}
          onChange={(e) => toggleCompleted(e.currentTarget.checked)}
          className={classes.checkbox}
        />
        {task.assigned_to_user && (
          <Link href={route("users.edit", task.assigned_to_user.id)}>
            <Pill size="sm" className={classes.user}>
              {shortName(task.assigned_to_user.name)}
            </Pill>
          </Link>
        )}
        <Text key={task.id} size="sm" fw={500}>
          #{task.number + ": " + task.name}
        </Text>
      </Group>
    </Flex>
  );
}
