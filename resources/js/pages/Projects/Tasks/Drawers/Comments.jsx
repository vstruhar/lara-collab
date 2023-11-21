import RichTextEditor from "@/components/RichTextEditor";
import { dateTime, diffForHumans } from "@/utils/datetime";
import {
  Avatar,
  Box,
  Button,
  Center,
  Flex,
  Group,
  Loader,
  Stack,
  Text,
  Title,
  Tooltip,
} from "@mantine/core";
import axios from "axios";
import { useEffect, useState } from "react";
import classes from "./css/Comments.module.css";

export default function Comments({ task }) {
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [comment, setComment] = useState("");

  useEffect(() => {
    axios
      .get(route("projects.tasks.comments", [task.project_id, task.id]))
      .then(({ data }) => setComments(data))
      .finally(() => setLoading(false))
      .catch((e) => {
        console.error(e);
        alert("Failed to load comments");
      });
  }, []);

  const submit = () => {
    axios
      .post(
        route("projects.tasks.comments.store", [task.project_id, task.id]),
        { content: comment },
        { progress: true },
      )
      .then(({ data }) => {
        setComment("");
        setComments([data.comment, ...comments]);
      })
      .catch((e) => {
        console.error(e);
        alert("Failed to save comment");
      });
  };

  return (
    <Box mb="xl">
      <Title order={3} mt="xl">
        Discussion
        {!loading && (
          <Text c="dimmed" fw={500} display="inline-block" ml={5}>
            ({comments.length})
          </Text>
        )}
      </Title>
      <RichTextEditor
        mt="md"
        placeholder="Write a comment"
        height={100}
        content={comment}
        onChange={(content) => setComment(content)}
      />
      <Flex justify="flex-end">
        <Button
          variant="filled"
          mt="md"
          disabled={comment.length <= 7}
          onClick={submit}
        >
          Add comment
        </Button>
      </Flex>

      {loading ? (
        <Center mih={100}>
          <Loader color="blue" />
        </Center>
      ) : (
        <Stack gap={30} mt="md">
          {comments.map((comment) => (
            <div key={comment.id}>
              <Group justify="space-between">
                <Group>
                  <Avatar src={comment.user.avatar} radius="xl" />
                  <div>
                    <Text size="sm" c="blue" fw={500}>
                      {comment.user.name}
                    </Text>
                    <Text size="xs" c="dimmed">
                      {comment.user.job_title}
                    </Text>
                  </div>
                </Group>
                <Tooltip
                  label={dateTime(comment.created_at)}
                  openDelay={250}
                  withArrow
                >
                  <Text size="xs" c="dimmed">
                    {diffForHumans(comment.created_at)}
                  </Text>
                </Tooltip>
              </Group>
              <Text
                pl={54}
                pt={6}
                size="sm"
                className={classes.comment}
                dangerouslySetInnerHTML={{ __html: comment.content }}
              ></Text>
            </div>
          ))}
        </Stack>
      )}
    </Box>
  );
}
