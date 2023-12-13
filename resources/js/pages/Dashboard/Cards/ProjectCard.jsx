import Card from "@/components/Card";
import { redirectTo } from "@/utils/route";
import { Group, RingProgress, Stack, Text, Title, Tooltip, rem } from "@mantine/core";
import { IconStarFilled } from "@tabler/icons-react";
import round from "lodash/round";
import classes from "./css/ProjectCard.module.css";

export function ProjectCard({ project }) {
  let completedPercent = 0;
  let overduePercent = 0;

  if (project.all_tasks_count > 0) {
    completedPercent = (project.completed_tasks_count / project.all_tasks_count) * 100;
    overduePercent = (project.overdue_tasks_count / project.all_tasks_count) * 100;
  }

  return (
    <Card bg="none">
      <Group justify="space-between" wrap="nowrap">
        <Stack gap={6}>
          <Group justify="space-between">
            <Title
              fz={24}
              onClick={() => redirectTo("projects.tasks", project.id)}
              className={classes.link}
            >
              {project.favorite && (
                <IconStarFilled
                  style={{
                    color: "var(--mantine-color-yellow-4)",
                    width: rem(15),
                    height: rem(15),
                    marginRight: 10,
                  }}
                />
              )}
              {project.name}
            </Title>
          </Group>
          <Text fz="xs" fw={700} c="dimmed" mb={4}>
            {project.client_company.name}
          </Text>
          <div>
            <Tooltip label="Completed tasks" openDelay={500} withArrow>
              <Text fz="lg" fw={500} inline span>
                Tasks: {project.completed_tasks_count} / {project.all_tasks_count}
              </Text>
            </Tooltip>
          </div>
        </Stack>
        <RingProgress
          size={100}
          thickness={10}
          sections={[
            { value: 100 - (completedPercent + overduePercent), color: "gray" },
            {
              value: overduePercent,
              color: "red",
              tooltip: `Overdue: ${project.overdue_tasks_count}`,
            },
            {
              value: completedPercent,
              color: "blue",
              tooltip: `Completed: ${project.completed_tasks_count}`,
            },
          ]}
          label={
            <Text fz={15} fw={700} ta="center">
              {round(completedPercent)}%
            </Text>
          }
        />
      </Group>
    </Card>
  );
}
