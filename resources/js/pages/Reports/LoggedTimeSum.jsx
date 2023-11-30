import useForm from "@/hooks/useForm";
import ContainerBox from "@/layouts/ContainerBox";
import Layout from "@/layouts/MainLayout";
import { money } from "@/utils/currency";
import { currentUrlParams } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import {
  Accordion,
  Box,
  Breadcrumbs,
  Button,
  Center,
  Checkbox,
  Group,
  MultiSelect,
  Table,
  Text,
  Title,
  rem,
} from "@mantine/core";
import { DatePickerInput, DatesProvider } from "@mantine/dates";
import { IconClock } from "@tabler/icons-react";
import dayjs from "dayjs";
import round from "lodash/round";

const LoggedTimeSum = () => {
  let { projects, dropdowns } = usePage().props;

  const params = currentUrlParams();

  const [form, submit, updateValue] = useForm("get", route("reports.logged-time.sum"), {
    projects: params.projects?.map(String) || [],
    users: params.users?.map(String) || [],
    dateRange:
      params.dateRange && params.dateRange[0] && params.dateRange[1]
        ? [dayjs(params.dateRange[0]).toDate(), dayjs(params.dateRange[1]).toDate()]
        : [],
    completed: params.completed !== undefined ? params.completed : true,
    billable: params.billable !== undefined ? params.billable : true,
  });

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <div>Reports</div>
        <div>Logged time sum</div>
      </Breadcrumbs>

      <Title order={1} mb={20}>
        Logged time sum
      </Title>

      <ContainerBox px={35} py={25}>
        <form onSubmit={submit}>
          <Group justify="space-between">
            <Group gap="xl">
              <MultiSelect
                placeholder={form.data.projects.length ? null : "Select projects"}
                required
                w={220}
                value={form.data.projects}
                onChange={(values) => updateValue("projects", values)}
                data={dropdowns.projects}
                error={form.errors.projects}
              />

              <MultiSelect
                placeholder={form.data.users.length ? null : "Select users"}
                required
                w={220}
                value={form.data.users}
                onChange={(values) => updateValue("users", values)}
                data={dropdowns.users}
                error={form.errors.users}
              />

              <DatesProvider settings={{ timezone: "utc" }}>
                <DatePickerInput
                  type="range"
                  valueFormat="MMM D"
                  placeholder="Pick dates range"
                  clearable
                  allowSingleDateInRange
                  miw={200}
                  value={form.data.dateRange}
                  onChange={(dates) => updateValue("dateRange", dates)}
                />
              </DatesProvider>

              <Checkbox
                label="Billable"
                checked={form.data.billable}
                onChange={(event) => updateValue("billable", event.currentTarget.checked)}
              />

              <Checkbox
                label="Completed"
                checked={form.data.completed}
                onChange={(event) => updateValue("completed", event.currentTarget.checked)}
              />
            </Group>

            <Button type="submit" disabled={form.processing}>
              Submit
            </Button>
          </Group>
        </form>
      </ContainerBox>

      <Box mt="xl">
        {Object.keys(projects).length ? (
          <Accordion
            variant="separated"
            radius="md"
            multiple
            defaultValue={Object.keys(projects).map((i) => i.toString())}
          >
            {Object.keys(projects).map((projectId) => (
              <Accordion.Item key={projectId} value={projectId.toString()}>
                <Accordion.Control>
                  <Text fz={20} fw={600}>
                    {projects[projectId][0].project_name}
                  </Text>
                </Accordion.Control>
                <Accordion.Panel>
                  <Table horizontalSpacing="xl" verticalSpacing="md" striped highlightOnHover>
                    <Table.Thead>
                      <Table.Tr>
                        <Table.Th>User</Table.Th>
                        <Table.Th>Rate</Table.Th>
                        <Table.Th>Logged time</Table.Th>
                        <Table.Th>Expense</Table.Th>
                      </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                      {projects[projectId].map((row) => (
                        <Table.Tr key={row.user_id}>
                          <Table.Td>{row.user_name}</Table.Td>
                          <Table.Td>{money(row.user_rate)}</Table.Td>
                          <Table.Td>{round(row.total_hours, 2).toFixed(2)} h</Table.Td>
                          <Table.Td>{money(row.total_hours * row.user_rate)}</Table.Td>
                        </Table.Tr>
                      ))}
                    </Table.Tbody>
                  </Table>
                </Accordion.Panel>
              </Accordion.Item>
            ))}
          </Accordion>
        ) : (
          <Center mih={300}>
            <Group gap={20}>
              <IconClock
                style={{
                  width: rem(55),
                  height: rem(55),
                }}
              />
              <div>
                <Text fz={24} fw={600}>
                  No logged time found
                </Text>
                <Text fz={15} c="dimmed">
                  Try changing selected filters
                </Text>
              </div>
            </Group>
          </Center>
        )}
      </Box>
    </>
  );
};

LoggedTimeSum.layout = (page) => <Layout title="Logged time sum">{page}</Layout>;

export default LoggedTimeSum;
