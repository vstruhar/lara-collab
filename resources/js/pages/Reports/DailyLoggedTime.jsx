import EmptyWithIcon from "@/components/EmptyWithIcon";
import useForm from "@/hooks/useForm";
import ContainerBox from "@/layouts/ContainerBox";
import Layout from "@/layouts/MainLayout";
import { currentUrlParams } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import {
  Box,
  Breadcrumbs,
  Button,
  Center,
  Checkbox,
  Group,
  MultiSelect,
  Table,
  Title,
} from "@mantine/core";
import { DatePickerInput, DatesProvider } from "@mantine/dates";
import { IconClock } from "@tabler/icons-react";
import dayjs from "dayjs";
import { round } from "lodash";

const DailyLoggedTime = () => {
  let { items, users, dropdowns } = usePage().props;

  const params = currentUrlParams();

  const [form, submit, updateValue] = useForm("get", route("reports.logged-time.daily"), {
    projects: params.projects?.map(String) || [],
    users: params.users?.map(String) || [],
    dateRange:
      params.dateRange && params.dateRange[0] && params.dateRange[1]
        ? [dayjs(params.dateRange[0]).toDate(), dayjs(params.dateRange[1]).toDate()]
        : [dayjs().subtract(1, "week").toDate(), dayjs().toDate()],
    completed: params.completed !== undefined ? params.completed : true,
    billable: params.billable !== undefined ? params.billable : true,
  });

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <div>Reports</div>
        <div>Daily logged time</div>
      </Breadcrumbs>

      <Title order={1} mb={20}>
        Daily logged time
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
        {Object.keys(items).length ? (
          <ContainerBox px={35} py={15}>
            <Table horizontalSpacing="xl" verticalSpacing="md" striped highlightOnHover>
              <Table.Thead>
                <Table.Tr>
                  <Table.Th>Date</Table.Th>
                  {Object.keys(users).map((key) => (
                    <Table.Th key={users[key].user_id}>{users[key].user_name}</Table.Th>
                  ))}
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody>
                {Object.keys(items).map((date) => (
                  <Table.Tr key={date}>
                    <Table.Td>{date}</Table.Td>
                    {Object.keys(users).map((userId) => (
                      <Table.Td key={`${date}-${userId}`}>
                        {round(items[date][userId]?.total_hours || 0, 2).toFixed(2)} h
                      </Table.Td>
                    ))}
                  </Table.Tr>
                ))}
                <Table.Tr fw={800}>
                  <Table.Td align="right">SUM</Table.Td>
                  {Object.keys(users).map((userId) => (
                    <Table.Td key={userId}>
                      {Object.keys(items)
                        .reduce(
                          (acc, date) => acc + Number(items[date][userId]?.total_hours || 0),
                          0,
                        )
                        .toFixed(2)}{" "}
                      h
                    </Table.Td>
                  ))}
                </Table.Tr>
              </Table.Tbody>
            </Table>
          </ContainerBox>
        ) : (
          <Center mih={300}>
            <EmptyWithIcon
              title="No logged time found"
              subtitle="Try changing selected filters"
              icon={IconClock}
            />
          </Center>
        )}
      </Box>
    </>
  );
};

DailyLoggedTime.layout = (page) => <Layout title="Logged time sum">{page}</Layout>;

export default DailyLoggedTime;
