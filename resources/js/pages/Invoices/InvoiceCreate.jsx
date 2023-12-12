import ActionButton from "@/components/ActionButton";
import BackButton from "@/components/BackButton";
import { Label } from "@/components/Label";
import useForm from "@/hooks/useForm";
import ContainerBox from "@/layouts/ContainerBox";
import { money } from "@/utils/currency";
import { date } from "@/utils/datetime";
import { openInNewTab, redirectTo } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import {
  Anchor,
  Box,
  Breadcrumbs,
  Center,
  Checkbox,
  Flex,
  Grid,
  Group,
  MultiSelect,
  NumberInput,
  Radio,
  Select,
  SimpleGrid,
  Stack,
  Text,
  TextInput,
  Textarea,
  Title,
  Tooltip,
  rem,
} from "@mantine/core";
import { useDidUpdate } from "@mantine/hooks";
import { IconSearch } from "@tabler/icons-react";
import axios from "axios";
import { useState } from "react";

export const InvoiceCreate = () => {
  const { projects, clientCompanies, nextNumber } = usePage().props;
  const [filteredProjects, setFilteredProjects] = useState([]);
  const [currency, setCurrency] = useState("");
  const [projectTasks, setProjectTasks] = useState([]);
  const [total, setTotal] = useState(0);

  const [form, submit, updateValue] = useForm("post", route("invoices.store"), {
    number: nextNumber,
    client_company_id: "",
    projects: [],
    tasks: [],
    type: "hourly",
    hourly_rate: 0,
    fixed_amount: 0,
    note: "",
  });

  useDidUpdate(() => {
    setFilteredProjects(
      projects
        .filter((i) => i.client_company_id == form.data.client_company_id)
        .map((i) => ({ value: i.id.toString(), label: i.name })),
    );

    const company = clientCompanies.find((i) => form.data.client_company_id === i.id.toString());

    if (company.rate) {
      updateValue("hourly_rate", company.rate / 100);
    }
    setCurrency(company.currency);
  }, [form.data.client_company_id]);

  useDidUpdate(() => fetchTasks(), [form.data.projects]);

  useDidUpdate(() => {
    let total = 0;

    if (form.data.type === "hourly") {
      projectTasks.forEach((project) => {
        project.tasks.forEach((task) => {
          if (form.data.tasks.includes(task.id))
            total += (Number(task.total_minutes) / 60) * form.data.hourly_rate * 100;
        });
      });
    } else {
      total = form.data.fixed_amount;
    }
    setTotal(total);
  }, [form.data.tasks, form.data.type, form.data.hourly_rate, form.data.fixed_amount]);

  const fetchTasks = () => {
    if (form.data.projects.length) {
      axios
        .get(route("invoices.tasks", { projectIds: form.data.projects }))
        .then(({ data }) => {
          setProjectTasks(data.projectTasks);
          const taskIds = [];

          data.projectTasks.forEach((project) => {
            project.tasks.forEach(
              (task) => Number(task.total_minutes) > 0 && taskIds.push(task.id),
            );
          });
          updateValue("tasks", [...taskIds]);
        })
        .catch((error) => console.error("Failed to fetch tasks", error));
    }
  };

  const toggleTask = (taskId, checked) => {
    updateValue(
      "tasks",
      checked ? [...form.data.tasks, taskId] : form.data.tasks.filter((id) => id !== taskId),
    );
  };

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <Anchor href="#" onClick={() => redirectTo("invoices.index")} fz={14}>
          Invoices
        </Anchor>
        <div>Create</div>
      </Breadcrumbs>

      <Grid justify="space-between" align="flex-end" gutter="xl" mb="lg">
        <Grid.Col span="auto">
          <Title order={1}>Create invoice</Title>
        </Grid.Col>
        <Grid.Col span="content"></Grid.Col>
      </Grid>

      <SimpleGrid cols={2} spacing="xl">
        <ContainerBox>
          <form onSubmit={submit}>
            <TextInput
              label="Invoice number"
              placeholder="Invoice number"
              required
              value={form.data.number}
              onChange={(e) => updateValue("number", e.target.value)}
              error={form.errors.number}
            />

            <Select
              label="Client company"
              placeholder="Select client company"
              searchable={true}
              allowDeselect={false}
              mt="md"
              required
              value={form.data.client_company_id}
              onChange={(value) => updateValue("client_company_id", value)}
              data={clientCompanies.map((i) => ({ value: i.id.toString(), label: i.name }))}
              error={form.errors.client_company_id}
            />

            <MultiSelect
              label="Projects"
              placeholder={
                filteredProjects.length ? "Select projects" : "Please select client company first"
              }
              disabled={filteredProjects.length === 0}
              withAsterisk
              mt="md"
              value={form.data.projects}
              onChange={(values) => updateValue("projects", values)}
              data={filteredProjects}
              error={form.errors.projects}
            />

            <Radio.Group
              label="Payment type"
              mt="md"
              withAsterisk
              value={form.data.type}
              onChange={(value) => updateValue("type", value)}
            >
              <Group mt="xs">
                <Radio value="hourly" label="Hourly" />
                <Radio value="fixed_amount" label="Fixed amount" />
              </Group>
            </Radio.Group>

            {form.data.type === "hourly" && (
              <NumberInput
                label="Hourly rate"
                mt="md"
                allowNegative={false}
                clampBehavior="strict"
                decimalScale={2}
                fixedDecimalScale={true}
                prefix={currency.symbol}
                value={form.data.hourly_rate}
                onChange={(value) => updateValue("hourly_rate", value)}
                error={form.errors.hourly_rate}
              />
            )}

            {form.data.type === "fixed_amount" && (
              <NumberInput
                label="Fixed amount"
                mt="md"
                allowNegative={false}
                clampBehavior="strict"
                decimalScale={2}
                fixedDecimalScale={true}
                prefix={currency.symbol}
                value={form.data.fixed_amount / 100}
                onChange={(value) => updateValue("fixed_amount", value * 100)}
                error={form.errors.fixed_amount}
              />
            )}

            <Textarea
              label="Note"
              placeholder="Invoice note"
              mt="md"
              autosize
              minRows={4}
              maxRows={8}
              value={form.data.note}
              onChange={(e) => updateValue("note", e.target.value)}
            />

            <Group justify="space-between" mt="xl">
              <BackButton route="invoices.index" />
              <ActionButton loading={form.processing}>Create</ActionButton>
            </Group>
          </form>
        </ContainerBox>
        <ContainerBox>
          {projectTasks.length > 0 ? (
            <>
              {projectTasks.map((project) => (
                <Box key={project.id} mb="lg">
                  <Title order={2} mb="md">
                    {project.name}
                  </Title>
                  {project.tasks.length ? (
                    project.tasks.map((task) => (
                      <Flex key={task.id} justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap" align="self-start">
                          <Checkbox
                            size="sm"
                            checked={form.data.tasks.includes(task.id)}
                            onChange={(event) => toggleTask(task.id, event.currentTarget.checked)}
                          />
                          <Stack gap={3}>
                            <Text
                              size="sm"
                              fw={500}
                              onClick={() =>
                                openInNewTab("projects.tasks.open", [task.project_id, task.id])
                              }
                            >
                              #{task.number + ": " + task.name}
                            </Text>

                            <Group wrap="wrap" style={{ rowGap: rem(3), columnGap: rem(12) }}>
                              {task.labels.map((label) => (
                                <Label key={label.id} name={label.name} color={label.color} />
                              ))}
                            </Group>
                          </Stack>
                        </Group>
                        <Stack gap={3} ml={10} style={{ flexShrink: 0 }}>
                          {form.data.type === "hourly" && (
                            <Tooltip
                              label={
                                Number(task.total_minutes) === 0
                                  ? "There is no logged time on this task"
                                  : `Logged time: ${Number(task.total_minutes) / 60}h`
                              }
                              openDelay={500}
                              withArrow
                            >
                              <Text fw={700} c={Number(task.total_minutes) === 0 ? "red" : ""}>
                                {money(
                                  (Number(task.total_minutes) / 60) * form.data.hourly_rate * 100,
                                  currency.code,
                                )}
                              </Text>
                            </Tooltip>
                          )}
                          <Text size="xs" c="dimmed" fw={500}>
                            {date(task.completed_at)}
                          </Text>
                        </Stack>
                      </Flex>
                    ))
                  ) : (
                    <Text size="sm" c="dimmed">
                      No tasks with logged time were found
                    </Text>
                  )}
                </Box>
              ))}
              <Flex justify="flex-end" mt="xl">
                <Stack gap={0}>
                  <Text size="lg" lts={1} fw={600} mb={-5}>
                    Total:
                  </Text>
                  <Text fw={700} fz={32}>
                    {money(total, currency.code)}
                  </Text>
                </Stack>
              </Flex>
            </>
          ) : (
            <>
              <Center mih={300}>
                <Box align="center">
                  <IconSearch style={{ width: rem(55), height: rem(55) }} opacity={0.5} />
                  <Text fz={24} fw={600} align="center">
                    No tasks found
                  </Text>
                  <Text fz={15} c="dimmed">
                    Select company and at least one project
                  </Text>
                </Box>
              </Center>
            </>
          )}
        </ContainerBox>
      </SimpleGrid>
    </>
  );
};
