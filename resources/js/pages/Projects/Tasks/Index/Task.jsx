import { Text } from "@mantine/core";

export default function Task({ task, ...props }) {
  return (
    <div {...props}>
      <Text key={task.id} size="xl" fw={700}>
        {task.name}
      </Text>
    </div>
  );
}
