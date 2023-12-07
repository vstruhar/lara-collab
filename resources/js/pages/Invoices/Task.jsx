import { Label } from "@/components/Label";
import { money } from "@/utils/currency";
import { date } from "@/utils/datetime";
import { openInNewTab } from "@/utils/route";
import { Checkbox, Flex, Group, Stack, Text, Tooltip, rem } from "@mantine/core";
import classes from "./css/Task.module.css";

export default function Task({ task, selectedTasks, toggleTask, currency, type, hourlyRate }) {
  return (
    <Flex className={classes.task} justify="space-between" wrap="nowrap">
      <Group gap="sm" wrap="nowrap" align="self-start">
        <Checkbox
          size="sm"
          className={classes.checkbox}
          checked={selectedTasks.includes(task.id)}
          onChange={(event) => toggleTask(task.id, event.currentTarget.checked)}
        />
        <Stack gap={3}>
          <Text
            className={classes.name}
            size="sm"
            fw={500}
            onClick={() => openInNewTab("projects.tasks.open", [task.project_id, task.id])}
          >
            #{task.number + ": " + task.name}
          </Text>

          <Group
            wrap="wrap"
            style={{
              rowGap: rem(3),
              columnGap: rem(12),
            }}
          >
            {task.labels.map((label) => (
              <Label key={label.id} name={label.name} color={label.color} />
            ))}
          </Group>
        </Stack>
      </Group>
      <Stack
        gap={3}
        ml={10}
        style={{
          flexShrink: 0,
        }}
      >
        {type === "hourly" && (
          <Tooltip
            label={
              Number(task.total_minutes) === 0
                ? "There is no logged time on this task"
                : `Logged time: ${Number(task.total_minutes) / 60}h`
            }
            openDelay={500}
            withArrow
          >
            <Text fw={700} c={Number(task.total_minutes) === 0 ? "red.7" : ""}>
              {money((Number(task.total_minutes) / 60) * hourlyRate, currency.code)}
            </Text>
          </Tooltip>
        )}
        <Text size="xs" c="dimmed" fw={500}>
          {date(task.completed_at)}
        </Text>
      </Stack>
    </Flex>
  );
}
