import Card from "@/components/Card";
import { date, diffForHumans } from "@/utils/datetime";
import { redirectTo } from "@/utils/route";
import {
  Box,
  Center,
  Divider,
  Group,
  Pill,
  ScrollArea,
  Stack,
  Text,
  Title,
  Tooltip,
  rem,
} from "@mantine/core";
import { IconRocket } from "@tabler/icons-react";
import classes from "./css/OverdueTasks.module.css";

export default function OverdueTasks({ tasks }) {
  return (
    <Card bg="none">
      <Title order={3} ml={15}>
        Overdue tasks
      </Title>

      <Divider my={14} />

      {tasks.length > 0 ? (
        <ScrollArea h={300} scrollbarSize={7}>
          <Stack gap={10}>
            {tasks.map((task) => (
              <Box key={task.id} className={classes.task}>
                <Group wrap="nowrap" justify="space-between">
                  <Stack gap={3}>
                    <Text
                      fz={13}
                      fw={600}
                      onClick={() => redirectTo("projects.tasks.open", [task.project_id, task.id])}
                      className={classes.link}
                    >
                      {task.name}
                    </Text>
                    <Group>
                      <Tooltip label="Task group" openDelay={500} withArrow>
                        <Pill size="xs" className={classes.user} bg="blue" c="white">
                          {task.task_group.name}
                        </Pill>
                      </Tooltip>
                      <Text fz={11} c="dimmed">
                        {task.project.name}
                      </Text>
                    </Group>
                  </Stack>
                  <Tooltip label={date(task.due_on)} openDelay={500} withArrow>
                    <Text fz={11} c="red" fw={700} className={classes.due}>
                      {diffForHumans(task.due_on)}
                    </Text>
                  </Tooltip>
                </Group>
              </Box>
            ))}
          </Stack>
        </ScrollArea>
      ) : (
        <Center my={30}>
          <Group gap={15}>
            <IconRocket
              style={{
                width: rem(45),
                height: rem(45),
              }}
            />
            <div>
              <Text fz={24} fw={600}>
                All done!
              </Text>
              <Text fz={15} c="dimmed">
                You have no overdue tasks
              </Text>
            </div>
          </Group>
        </Center>
      )}
    </Card>
  );
}
