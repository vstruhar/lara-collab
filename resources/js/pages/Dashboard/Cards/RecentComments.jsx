import Card from "@/components/Card";
import { dateTime, diffForHumans } from "@/utils/datetime";
import { redirectTo } from "@/utils/route";
import {
  Avatar,
  Box,
  Center,
  Divider,
  Group,
  ScrollArea,
  Stack,
  Text,
  Title,
  Tooltip,
  rem,
} from "@mantine/core";
import { IconMessage } from "@tabler/icons-react";
import classes from "./css/RecentComments.module.css";

export default function RecentComments({ comments }) {
  return (
    <Card bg="none">
      <Title order={3} ml={15}>
        Recent comments
      </Title>

      <Divider my={14} />

      {comments.length > 0 ? (
        <ScrollArea h={300} scrollbarSize={7}>
          <Stack gap={6}>
            {comments.map((comment) => (
              <Box
                key={comment.id}
                className={classes.item}
                onClick={() =>
                  redirectTo("projects.tasks.open", [comment.task.project_id, comment.task_id])
                }
              >
                <Group justify="space-between">
                  <Group gap="xs" align="start">
                    <Avatar src={comment.user.avatar} radius="xl" color="blue" />
                    <div>
                      <Text size="sm" c="blue" fw={600}>
                        {comment.user.name}
                      </Text>
                      <Text fz={11} fw={500} c="dimmed">
                        {comment.task.project.name}
                      </Text>
                    </div>
                  </Group>
                  <Tooltip label={dateTime(comment.created_at)} openDelay={250} withArrow>
                    <Text size="xs">{diffForHumans(comment.created_at)}</Text>
                  </Tooltip>
                </Group>
                <Text
                  pl={49}
                  fz={11}
                  mt={5}
                  className={classes.comment}
                  dangerouslySetInnerHTML={{ __html: comment.content }}
                ></Text>
              </Box>
            ))}
          </Stack>
        </ScrollArea>
      ) : (
        <Center my={30}>
          <Group gap={15} opacity={0.5}>
            <IconMessage
              style={{
                width: rem(40),
                height: rem(40),
              }}
            />
            <div>
              <Text fz={20} fw={600}>
                No comments
              </Text>
              <Text fz={12} c="dimmed">
                On your tasks
              </Text>
            </div>
          </Group>
        </Center>
      )}
    </Card>
  );
}
