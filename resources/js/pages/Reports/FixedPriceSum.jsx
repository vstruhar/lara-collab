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
  Title,
} from '@mantine/core';
import { DatePickerInput, DatesProvider } from '@mantine/dates';
import { IconCurrencyDollar } from '@tabler/icons-react';
import dayjs from 'dayjs';

const FixedPriceSum = () => {
  let { users, dropdowns } = usePage().props;

  const params = currentUrlParams();

  const [form, submit, updateValue] = useForm('get', route('reports.fixed-price.sum'), {
    projects: params.projects?.map(String) || [],
    users: params.users?.map(String) || [],
    dateRange:
      params.dateRange && params.dateRange[0] && params.dateRange[1]
        ? [dayjs(params.dateRange[0]).toDate(), dayjs(params.dateRange[1]).toDate()]
        : [dayjs().subtract(1, 'week').toDate(), dayjs().toDate()],
    completed: params.completed !== undefined ? params.completed : true,
    billable: params.billable !== undefined ? params.billable : true,
  });

  const getTotalFixedPrice = () => {
    return users.reduce((total, user) => total + parseInt(user.total_fixed_price), 0);
  };

  const getTotalTasks = () => {
    return users.reduce((total, user) => total + parseInt(user.total_tasks), 0);
  };

  return (
    <>
      <Breadcrumbs
        fz={14}
        mb={30}
      >
        <div>Reports</div>
        <div>Fixed price sum</div>
      </Breadcrumbs>

      <Title
        order={1}
        mb={20}
      >
        Fixed price sum
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
        {users.length ? (
          <ContainerBox
            px={35}
            py={15}
            my={25}
          >
            <Table
              horizontalSpacing='xl'
              verticalSpacing='md'
              striped
              highlightOnHover
            >
              <Table.Thead>
                <Table.Tr>
                  <Table.Th>User</Table.Th>
                  <Table.Th>Total Tasks</Table.Th>
                  <Table.Th>Total Fixed Price</Table.Th>
                </Table.Tr>
              </Table.Thead>
              <Table.Tbody>
                {users.map(user => (
                  <Table.Tr key={user.user_id}>
                    <Table.Td>{user.user_name}</Table.Td>
                    <Table.Td>{user.total_tasks}</Table.Td>
                    <Table.Td>{money(user.total_fixed_price)}</Table.Td>
                  </Table.Tr>
                ))}
                <Table.Tr style={{ fontWeight: 'bold', borderTop: '2px solid #e9ecef' }}>
                  <Table.Td>Total</Table.Td>
                  <Table.Td>{getTotalTasks()}</Table.Td>
                  <Table.Td>{money(getTotalFixedPrice())}</Table.Td>
                </Table.Tr>
              </Table.Tbody>
            </Table>
          </ContainerBox>
        ) : (
          <Center mih={300}>
            <EmptyWithIcon
              title='No fixed price tasks found'
              subtitle='Try changing selected filters'
              icon={IconCurrencyDollar}
            />
          </Center>
        )}
      </Box>
    </>
  );
};

FixedPriceSum.layout = page => <Layout title='Fixed price sum'>{page}</Layout>;

export default FixedPriceSum;
