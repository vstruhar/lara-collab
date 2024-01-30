import EmptyWithIcon from '@/components/EmptyWithIcon';
import useForm from '@/hooks/useForm';
import ContainerBox from '@/layouts/ContainerBox';
import Layout from '@/layouts/MainLayout';
import { money } from '@/utils/currency';
import { currentUrlParams } from '@/utils/route';
import { usePage } from '@inertiajs/react';
import {
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
} from '@mantine/core';
import { DatePickerInput, DatesProvider } from '@mantine/dates';
import { IconClock } from '@tabler/icons-react';
import dayjs from 'dayjs';
import round from 'lodash/round';

const LoggedTimeSum = () => {
  let { projects, clientCompanies, dropdowns } = usePage().props;

  const params = currentUrlParams();

  const [form, submit, updateValue] = useForm('get', route('reports.logged-time.sum'), {
    projects: params.projects?.map(String) || [],
    users: params.users?.map(String) || [],
    dateRange:
      params.dateRange && params.dateRange[0] && params.dateRange[1]
        ? [dayjs(params.dateRange[0]).toDate(), dayjs(params.dateRange[1]).toDate()]
        : [dayjs().subtract(1, 'week').toDate(), dayjs().toDate()],
    completed: params.completed !== undefined ? params.completed : true,
    billable: params.billable !== undefined ? params.billable : true,
  });

  const formatMoney = (value, clientCompanyId) => {
    const currency = clientCompanies.find(i => i.id === clientCompanyId)?.currency;
    return money(value, currency?.code, currency?.decimals);
  };

  const calculateProfit = row => {
    return row.total_hours * row.project_rate - row.total_hours * row.user_rate;
  };

  return (
    <>
      <Breadcrumbs
        fz={14}
        mb={30}
      >
        <div>Reports</div>
        <div>Logged time sum</div>
      </Breadcrumbs>

      <Title
        order={1}
        mb={20}
      >
        Logged time sum
      </Title>

      <ContainerBox
        px={35}
        py={25}
      >
        <form onSubmit={submit}>
          <Group justify='space-between'>
            <Group gap='xl'>
              <MultiSelect
                placeholder={form.data.projects.length ? null : 'Select projects'}
                required
                w={220}
                value={form.data.projects}
                onChange={values => updateValue('projects', values)}
                data={dropdowns.projects}
                error={form.errors.projects}
              />

              <MultiSelect
                placeholder={form.data.users.length ? null : 'Select users'}
                required
                w={220}
                value={form.data.users}
                onChange={values => updateValue('users', values)}
                data={dropdowns.users}
                error={form.errors.users}
              />

              <DatesProvider settings={{ timezone: 'utc' }}>
                <DatePickerInput
                  type='range'
                  valueFormat='MMM D'
                  placeholder='Pick dates range'
                  clearable
                  allowSingleDateInRange
                  miw={200}
                  value={form.data.dateRange}
                  onChange={dates => updateValue('dateRange', dates)}
                />
              </DatesProvider>

              <Checkbox
                label='Billable'
                checked={form.data.billable}
                onChange={event => updateValue('billable', event.currentTarget.checked)}
              />

              <Checkbox
                label='Completed'
                checked={form.data.completed}
                onChange={event => updateValue('completed', event.currentTarget.checked)}
              />
            </Group>

            <Button
              type='submit'
              disabled={form.processing}
            >
              Submit
            </Button>
          </Group>
        </form>
      </ContainerBox>

      <Box mt='xl'>
        {Object.keys(projects).length ? (
          Object.keys(projects).map(projectId => (
            <ContainerBox
              key={projectId}
              px={35}
              py={15}
              my={25}
            >
              <Text
                fz={20}
                fw={600}
              >
                {projects[projectId][0].project_name}
              </Text>
              <Table
                horizontalSpacing='xl'
                verticalSpacing='md'
                striped
                highlightOnHover
              >
                <Table.Thead>
                  <Table.Tr>
                    <Table.Th>User</Table.Th>
                    <Table.Th>Logged time</Table.Th>
                    <Table.Th>Project rate</Table.Th>
                    <Table.Th>User rate</Table.Th>
                    <Table.Th>Expense</Table.Th>
                    <Table.Th>Profit</Table.Th>
                  </Table.Tr>
                </Table.Thead>
                <Table.Tbody>
                  {projects[projectId].map(row => (
                    <Table.Tr key={row.user_id}>
                      <Table.Td>{row.user_name}</Table.Td>
                      <Table.Td>{round(row.total_hours, 2).toFixed(2)} h</Table.Td>
                      <Table.Td>{formatMoney(row.project_rate, row.client_company_id)}</Table.Td>
                      <Table.Td opacity={row.user_rate === 0 ? 0.3 : 1}>
                        {formatMoney(row.user_rate, row.client_company_id)}
                      </Table.Td>
                      <Table.Td opacity={row.user_rate === 0 ? 0.3 : 1}>
                        {formatMoney(row.total_hours * row.user_rate, row.client_company_id)}
                      </Table.Td>
                      <Table.Td c={calculateProfit(row) < 0 ? 'red.7' : 'green.7'}>
                        {formatMoney(calculateProfit(row), row.client_company_id)}
                      </Table.Td>
                    </Table.Tr>
                  ))}
                </Table.Tbody>
              </Table>
            </ContainerBox>
          ))
        ) : (
          <Center mih={300}>
            <EmptyWithIcon
              title='No logged time found'
              subtitle='Try changing selected filters'
              icon={IconClock}
            />
          </Center>
        )}
      </Box>
    </>
  );
};

LoggedTimeSum.layout = page => <Layout title='Logged time sum'>{page}</Layout>;

export default LoggedTimeSum;
