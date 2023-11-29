import Layout from "@/layouts/MainLayout";
import { usePage } from "@inertiajs/react";
import { Accordion, Box, Breadcrumbs, Center, Group, Stack, Text, Title, rem } from "@mantine/core";
import { IconRocket, IconStar, IconStarFilled } from "@tabler/icons-react";
import Task from "./Task";

const TasksIndex = () => {
  let { projects } = usePage().props;

  projects = projects.filter((i) => i.tasks.length);

  let opened = projects.filter((i) => i.favorite).map((i) => i.id.toString());

  if (opened.length === 0) {
    opened = projects[0].id.toString();
  }

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <div>My Work</div>
        <div>Tasks</div>
      </Breadcrumbs>

      <Title order={1} mb={20}>
        Tasks assigned to you
      </Title>

      <Box maw={1000}>
        {projects.length ? (
          <Accordion variant="separated" radius="md" multiple defaultValue={opened}>
            {projects.map((project) => (
              <Accordion.Item key={project.id} value={project.id.toString()}>
                <Accordion.Control
                  icon={
                    project.favorite ? (
                      <IconStarFilled
                        style={{
                          color: "var(--mantine-color-yellow-4)",
                          width: rem(20),
                          height: rem(20),
                        }}
                      />
                    ) : (
                      <IconStar
                        style={{
                          width: rem(20),
                          height: rem(20),
                        }}
                      />
                    )
                  }
                >
                  <Text fz={20} fw={600}>
                    {project.name}
                  </Text>
                </Accordion.Control>
                <Accordion.Panel>
                  <Stack gap={8}>
                    {project.tasks.map((task) => (
                      <Task key={task.id} task={task} />
                    ))}
                  </Stack>
                </Accordion.Panel>
              </Accordion.Item>
            ))}
          </Accordion>
        ) : (
          <Center mih={300}>
            <Group gap={20}>
              <IconRocket
                style={{
                  width: rem(55),
                  height: rem(55),
                }}
              />
              <div>
                <Text fz={24} fw={600}>
                  All caught up!
                </Text>
                <Text fz={15} c="dimmed">
                  No tasks assigned at the moment
                </Text>
              </div>
            </Group>
          </Center>
        )}
      </Box>
    </>
  );
};

TasksIndex.layout = (page) => <Layout title="My Tasks">{page}</Layout>;

export default TasksIndex;
