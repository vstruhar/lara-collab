import ActionButton from '@/components/ActionButton';
import BackButton from '@/components/BackButton';
import useForm from '@/hooks/useForm';
import ContainerBox from '@/layouts/ContainerBox';
import Layout from '@/layouts/MainLayout';
import { money } from '@/utils/currency';
import { redirectTo } from '@/utils/route';
import { usePage } from '@inertiajs/react';
import {
  Anchor,
  Box,
  Breadcrumbs,
  Center,
  Flex,
  Grid,
  Group,
  MultiSelect,
  NumberInput,
  Checkbox,
  Select,
  Stack,
  Text,
  TextInput,
  Textarea,
  Title,
  rem,
} from '@mantine/core';
import { IconSearch } from '@tabler/icons-react';
import axios from 'axios';
import { useEffect, useState } from 'react';
import Task from './Task';
import { PricingType } from '@/utils/enums';

const InvoiceEdit = () => {
  const { invoice, projects, clientCompanies, selectedProjects } = usePage().props;
  const [filteredProjects, setFilteredProjects] = useState([]);
  const [currency, setCurrency] = useState('');
  const [projectTasks, setProjectTasks] = useState([]);
  const [total, setTotal] = useState(invoice.amount);

  const [form, submit, updateValue] = useForm('post', route('invoices.update', invoice.id), {
    _method: 'put',
    number: invoice.number,
    client_company_id: invoice.client_company_id.toString(),
    projects: selectedProjects.map(String),
    tasks: [],
    type: invoice.type,
    hourly_rate: invoice.hourly_rate,
    fixed_amount: invoice.type === 'fixed_amount' ? invoice.amount : 0,
    note: invoice.note || '',
  });

  useEffect(() => {
    setFilteredProjects(
      projects
        .filter(i => i.client_company_id == form.data.client_company_id)
        .map(i => ({ value: i.id.toString(), label: i.name }))
    );

    const company = clientCompanies.find(i => form.data.client_company_id === i.id.toString());
    setCurrency(company.currency);

    updateValue('projects', []);
  }, [form.data.client_company_id]);

  useEffect(() => {
    if (form.data.projects.length) {
      axios
        .get(route('invoices.tasks', { projectIds: form.data.projects, invoiceId: invoice.id }))
        .then(({ data }) => {
          setProjectTasks(data.projectTasks);
          const taskIds = [];

          data.projectTasks.forEach(project => {
            project.tasks.forEach(
              task =>
                invoice.tasks.find(i => i.id === task.id) !== undefined && taskIds.push(task.id)
            );
          });
          updateValue({
            ...form.data,
            tasks: [...taskIds],
          });
        })
        .catch(error => console.error('Failed to fetch tasks', error));
    }
  }, [form.data.projects]);

  useEffect(() => {
    let total = 0;

    if (form.data.type === 'default') {
      projectTasks.forEach(project => {
        project.tasks.forEach(task => {
          if (form.data.tasks.includes(task.id)) {
            if (task.pricing_type === PricingType.HOURLY) {
              total += (Number(task.total_minutes) / 60) * form.data.hourly_rate;
            } else {
              total += task.price;
            }
          }
        });
      });
    } else {
      total = form.data.fixed_amount;
    }
    setTotal(total);
  }, [form.data.tasks, form.data.type, form.data.hourly_rate, form.data.fixed_amount]);

  const toggleTask = (taskId, checked) => {
    updateValue(
      'tasks',
      checked ? [...form.data.tasks, taskId] : form.data.tasks.filter(id => id !== taskId)
    );
  };

  return (
    <>
      <Breadcrumbs
        fz={14}
        mb={30}
      >
        <Anchor
          href='#'
          onClick={() => redirectTo('invoices.index')}
          fz={14}
        >
          Invoices
        </Anchor>
        <div>Edit</div>
      </Breadcrumbs>

      <Grid
        justify='space-between'
        align='flex-end'
        gutter='xl'
        mb='lg'
      >
        <Grid.Col span='auto'>
          <Title order={1}>Edit invoice</Title>
        </Grid.Col>
        <Grid.Col span='content'></Grid.Col>
      </Grid>

      <Flex
        gap='xl'
        align='flex-start'
        direction='row'
        wrap='nowrap'
      >
        <ContainerBox miw='440'>
          <form onSubmit={submit}>
            <TextInput
              label='Invoice number'
              placeholder='Invoice number'
              required
              value={form.data.number}
              onChange={e => updateValue('number', e.target.value)}
              error={form.errors.number}
            />

            <Select
              label='Client company'
              placeholder='Select client company'
              searchable={true}
              allowDeselect={false}
              mt='md'
              required
              value={form.data.client_company_id?.toString()}
              onChange={value => updateValue('client_company_id', value)}
              data={clientCompanies.map(i => ({ value: i.id.toString(), label: i.name }))}
              error={form.errors.client_company_id}
            />

            <MultiSelect
              label='Projects'
              placeholder={
                filteredProjects.length ? 'Select projects' : 'Please select client company first'
              }
              disabled={filteredProjects.length === 0}
              withAsterisk
              mt='md'
              value={form.data.projects}
              onChange={values => updateValue('projects', values)}
              data={filteredProjects}
              error={form.errors.projects}
            />

            <Checkbox
              label='Fixed amount for whole invoice'
              mt='md'
              checked={form.data.type === 'fixed_amount'}
              onChange={event =>
                updateValue('type', event.currentTarget.checked ? 'fixed_amount' : 'default')
              }
            />

            {form.data.type === 'default' && (
              <NumberInput
                label='Hourly rate'
                mt='md'
                allowNegative={false}
                clampBehavior='strict'
                decimalScale={2}
                fixedDecimalScale={true}
                prefix={currency.symbol}
                value={form.data.hourly_rate / 100}
                onChange={value => updateValue('hourly_rate', value * 100)}
                error={form.errors.hourly_rate}
              />
            )}

            {form.data.type === 'fixed_amount' && (
              <NumberInput
                label='Fixed amount'
                mt='md'
                allowNegative={false}
                clampBehavior='strict'
                decimalScale={2}
                fixedDecimalScale={true}
                prefix={currency.symbol}
                value={form.data.fixed_amount / 100}
                onChange={value => updateValue('fixed_amount', value * 100)}
                error={form.errors.fixed_amount}
              />
            )}

            <Textarea
              label='Note'
              placeholder='Invoice note'
              mt='md'
              autosize
              minRows={4}
              maxRows={8}
              value={form.data.note}
              onChange={e => updateValue('note', e.target.value)}
            />

            <Group
              justify='space-between'
              mt={30}
            >
              <BackButton route='invoices.index' />
              <ActionButton loading={form.processing}>Update</ActionButton>
            </Group>
          </form>
        </ContainerBox>
        <ContainerBox style={{ flexGrow: 1 }}>
          {projectTasks.length > 0 ? (
            <>
              {projectTasks.map(project => (
                <Box
                  key={project.id}
                  mb='lg'
                >
                  <Title
                    order={2}
                    mb='md'
                  >
                    {project.name}
                  </Title>
                  {project.tasks.length ? (
                    project.tasks.map(task => (
                      <Task
                        key={task.id}
                        task={task}
                        selectedTasks={form.data.tasks}
                        toggleTask={toggleTask}
                        currency={currency}
                        type={form.data.type}
                        hourlyRate={form.data.hourly_rate}
                      />
                    ))
                  ) : (
                    <Text
                      size='sm'
                      c='dimmed'
                    >
                      No tasks with logged time were found
                    </Text>
                  )}
                </Box>
              ))}
              <Flex
                justify='flex-end'
                mt='xl'
              >
                <Stack gap={0}>
                  <Text
                    size='lg'
                    lts={1}
                    fw={600}
                    mb={-5}
                  >
                    Total:
                  </Text>
                  <Text
                    fw={700}
                    fz={32}
                  >
                    {money(total, currency.code)}
                  </Text>
                </Stack>
              </Flex>
            </>
          ) : (
            <>
              <Center mih={300}>
                <Box align='center'>
                  <IconSearch
                    style={{ width: rem(55), height: rem(55) }}
                    opacity={0.5}
                  />
                  <Text
                    fz={24}
                    fw={600}
                    align='center'
                  >
                    No tasks found
                  </Text>
                  <Text
                    fz={15}
                    c='dimmed'
                  >
                    Select company and at least one project
                  </Text>
                </Box>
              </Center>
            </>
          )}
        </ContainerBox>
      </Flex>
    </>
  );
};

InvoiceEdit.layout = page => <Layout title='Edit invoice'>{page}</Layout>;

export default InvoiceEdit;
