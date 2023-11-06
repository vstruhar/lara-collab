import { getInitials } from "@/services/UserService";
import { Avatar, Card, Group, Progress, Text, Tooltip } from "@mantine/core";
import ToggleFavorite from "./FavoriteToggle";
import ProjectCardActions from "./ProjectCardActions";

export default function ProjectCard({ item }) {
  return (
    <Card withBorder padding="xl" radius="md" w={350}>
      <Group justify="space-between">
        <Text size="xl" fw={700}>
          {item.name}
        </Text>
        <ToggleFavorite item={item} />
      </Group>

      <Text fz="sm" fw={500}>
        {item.client_company?.name}
      </Text>

      <Text fz="sm" c="dimmed" mt="lg">
        {item.description}
      </Text>

      <Text c="dimmed" fz="sm" mt="md">
        Tasks completed:{" "}
        <Text span fw={500} c="bright">
          0/36
        </Text>
      </Text>

      <Progress value={(0 / 36) * 100} mt={5} />

      <Group justify="space-between" mt="md">
        <Avatar.Group spacing="sm">
          {item.users_with_access.slice(0, 4).map((user) => (
            <Tooltip key={user.id} label={user.name} openDelay={300} withArrow>
              <Avatar src={user.avatar} radius="xl">
                {getInitials(user.name)}
              </Avatar>
            </Tooltip>
          ))}
          {item.users_with_access.length - 4 > 0 && (
            <Avatar radius="xl">+{item.users_with_access.length - 4}</Avatar>
          )}
        </Avatar.Group>

        <ProjectCardActions item={item} />
      </Group>
    </Card>
  );
}
