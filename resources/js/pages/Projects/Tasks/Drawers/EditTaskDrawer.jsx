import Dropzone from '@/components/Dropzone';
import RichTextEditor from '@/components/RichTextEditor';
import useTaskDrawerStore from '@/hooks/store/useTaskDrawerStore';
import useTasksStore from '@/hooks/store/useTasksStore';
import { date } from '@/utils/datetime';
import { hasRoles } from '@/utils/user';
import { usePage } from '@inertiajs/react';
import {
  Breadcrumbs,
  Checkbox,
  Drawer,
  Group,
  MultiSelect,
  NumberInput,
  Select,
  Text,
  TextInput,
  rem,
} from '@mantine/core';
import { DateInput } from '@mantine/dates';
import dayjs from 'dayjs';
import { useEffect, useRef, useState } from 'react';
import Comments from './Comments';
import LabelsDropdown from './LabelsDropdown';
import PriorityDropdown from './PriorityDropdown';
import Timer from './Timer';
import classes from './css/TaskDrawer.module.css';
import { PricingType } from '@/utils/enums';

export function EditTaskDrawer() {
  const editorRef = useRef(null);
  const editingFieldRef = useRef(null);
  const openedTaskIdRef = useRef(null);
  const appliedDescriptionRef = useRef(null);
  const { edit, openEditTask, closeEditTask } = useTaskDrawerStore();
  const { findTask, updateTaskProperty, complete, deleteAttachment, uploadAttachments } =
    useTasksStore();
  const {
    usersWithAccessToProject,
    taskGroups,
    labels,
    priorities,
    openedTask,
    currency,
    auth: { user },
  } = usePage().props;

  useEffect(() => {
    if (openedTask) setTimeout(() => openEditTask(openedTask), 50);
  }, []);

  const task = findTask(edit.task.id);

  const [data, setData] = useState({
    group_id: '',
    assigned_to_user_id: '',
    name: '',
    description: '',
    pricing_type: PricingType.HOURLY,
    estimation: 0,
    priority_id: '',
    fixed_price: 0,
    due_on: '',
    hidden_from_clients: false,
    billable: true,
    subscribed_users: [],
    labels: [],
  });

  useEffect(() => {
    if (!edit.opened || !task) {
      if (!edit.opened) {
        editingFieldRef.current = null;
        openedTaskIdRef.current = null;
        appliedDescriptionRef.current = null;
      }

      return;
    }

    const isDifferentTask = openedTaskIdRef.current !== task.id;

    if (isDifferentTask) {
      editingFieldRef.current = null;
      openedTaskIdRef.current = task.id;
      appliedDescriptionRef.current = null;
    }

    const editingField = editingFieldRef.current;
    const fixedPrice = task.fixed_price ? task.fixed_price / 100 : 0;

    setData((current) => ({
      group_id: task.group_id || '',
      assigned_to_user_id: task.assigned_to_user_id || '',
      name: editingField === 'name' ? current.name : task.name || '',
      description: editingField === 'description' ? current.description : task.description || '',
      pricing_type: task.pricing_type || PricingType.HOURLY,
      estimation: task.estimation || 0,
      priority_id: task.priority_id || '',
      fixed_price: editingField === 'fixed_price' ? current.fixed_price : fixedPrice,
      due_on: task.due_on ? dayjs(task.due_on).toDate() : '',
      hidden_from_clients: task.hidden_from_clients !== undefined ? task.hidden_from_clients : false,
      billable: task.billable !== undefined ? task.billable : true,
      subscribed_users: (task.subscribed_users || []).map((item) => item.id.toString()),
      labels: (task.labels || []).map((item) => item.id),
    }));

    const description = task.description || '';

    if (editingField !== 'description' && appliedDescriptionRef.current !== description) {
      appliedDescriptionRef.current = description;
      setTimeout(() => {
        editorRef.current?.setContent(description);
      }, 300);
    }
  }, [edit.opened, task]);

  const updateValue = (field, value) => {
    setData({ ...data, [field]: value });

    const dropdowns = ['labels', 'subscribed_users'];
    const onBlurInputs = ['name', 'description', 'fixed_price'];

    if (dropdowns.includes(field)) {
      const options = {
        labels: value.map(id => labels.find(i => i.id === id)),
        subscribed_users: value.map(id =>
          usersWithAccessToProject.find(i => i.id.toString() === id)
        ),
      };
      updateTaskProperty(task, field, value, options[field]);
    } else if (field === 'priority_id') {
      const priority = value ? priorities.find(p => p.id === value) : null;
      updateTaskProperty(task, field, value, priority);
    } else if (!onBlurInputs.includes(field)) {
      updateTaskProperty(task, field, value);
    }
  };

  const onBlurUpdate = property => {
    if (data.name.length > 0) {
      if (property === 'fixed_price') {
        updateTaskProperty(task, property, data[property] * 100);
      } else {
        updateTaskProperty(task, property, data[property]);
      }
    }
  };

  const handleFieldFocus = (field) => {
    editingFieldRef.current = field;
  };

  const handleFieldBlur = (field) => {
    editingFieldRef.current = null;
    onBlurUpdate(field);
  };

  const pricingTypes = [
    { value: PricingType.HOURLY, label: 'Hourly' },
    { value: PricingType.FIXED, label: 'Fixed' },
  ];

  const isFixedPrice = data.pricing_type === PricingType.FIXED;
  const currencySymbol = currency?.symbol || '';

  return (
    <Drawer
      opened={edit.opened}
      onClose={closeEditTask}
      title={
        <Group
          ml={25}
          my='sm'
          wrap='nowrap'
        >
          <Checkbox
            size='md'
            radius='xl'
            color='green'
            checked={task?.completed_at !== null}
            onChange={e => complete(task, e.currentTarget.checked)}
            className={can('complete task') ? classes.checkbox : classes.disabledCheckbox}
          />
          <Text
            fz={rem(27)}
            fw={600}
            lh={1.2}
            td={task?.completed_at !== null ? 'line-through' : null}
          >
            #{task?.number}: {data.name}
          </Text>
        </Group>
      }
      position='right'
      size={1000}
      overlayProps={{ backgroundOpacity: 0.55, blur: 3 }}
      transitionProps={{
        transition: 'slide-left',
        duration: 400,
        timingFunction: 'ease',
      }}
    >
      {task ? (
        <>
          <Breadcrumbs
            c='dark.3'
            ml={24}
            mb='xs'
            separator='I'
            separatorMargin='sm'
            styles={{ separator: { opacity: 0.3 } }}
          >
            <Text size='xs'>{task.project.name}</Text>
            <Text size='xs'>Task #{task.number}</Text>
            <Text size='xs'>
              Created by {task.created_by_user.name} on {date(task.created_at)}
            </Text>
          </Breadcrumbs>
          <form className={classes.inner}>
            <div className={classes.content}>
              <TextInput
                label='Name'
                placeholder='Task name'
                value={data.name}
                onChange={e => updateValue('name', e.target.value)}
                onFocus={() => handleFieldFocus('name')}
                onBlur={() => handleFieldBlur('name')}
                error={data.name.length === 0}
                readOnly={!can('edit task')}
              />

              <RichTextEditor
                ref={editorRef}
                mt='xl'
                placeholder='Task description'
                content={data.description}
                height={260}
                onChange={content => updateValue('description', content)}
                onFocus={() => handleFieldFocus('description')}
                onBlur={() => handleFieldBlur('description')}
                readOnly={!can('edit task')}
              />

              {can('edit task') && (
                <Dropzone
                  mt='xl'
                  selected={task.attachments}
                  onChange={files => uploadAttachments(task, files)}
                  remove={index => deleteAttachment(task, index)}
                />
              )}

              {can('view comments') && <Comments task={task} />}
            </div>
            <div className={classes.sidebar}>
              <Select
                label='Task group'
                placeholder='Select task group'
                allowDeselect={false}
                value={data.group_id.toString()}
                onChange={value => updateValue('group_id', value)}
                data={taskGroups.map(i => ({
                  value: i.id.toString(),
                  label: i.name,
                }))}
                readOnly={!can('edit task')}
              />

              <Select
                label='Assignee'
                placeholder='Select assignee'
                searchable
                mt='md'
                value={data.assigned_to_user_id?.toString()}
                onChange={value => updateValue('assigned_to_user_id', value)}
                data={usersWithAccessToProject.map(i => ({
                  value: i.id.toString(),
                  label: i.name,
                }))}
                readOnly={!can('edit task')}
              />

              <DateInput
                clearable
                valueFormat='DD MMM YYYY'
                minDate={new Date()}
                mt='md'
                label='Due date'
                placeholder='Pick task due date'
                value={data.due_on}
                onChange={value => updateValue('due_on', value)}
                readOnly={!can('edit task')}
              />

              <LabelsDropdown
                items={labels}
                selected={data.labels}
                onChange={values => updateValue('labels', values)}
                mt='md'
              />

              <NumberInput
                label='Time estimation'
                mt='md'
                decimalScale={2}
                fixedDecimalScale
                value={data.estimation}
                min={0}
                allowNegative={false}
                step={0.5}
                suffix=' hours'
                onChange={value => updateValue('estimation', value)}
                readOnly={!can('edit task')}
              />

              <PriorityDropdown
                value={data.priority_id}
                onChange={value => {
                  updateValue('priority_id', value || null);
                }}
                mt='md'
              />

              <Select
                label='Pricing type'
                placeholder='Select pricing type'
                mt='md'
                value={data.pricing_type}
                onChange={value => updateValue('pricing_type', value)}
                data={pricingTypes}
                readOnly={!can('edit task')}
              />

              {isFixedPrice && (can('view time logs') || can('add time log')) && (
                <NumberInput
                  label='Fixed price'
                  mt='md'
                  decimalScale={2}
                  fixedDecimalScale
                  value={data.fixed_price}
                  min={0}
                  allowNegative={false}
                  onChange={value => updateValue('fixed_price', value)}
                  onFocus={() => handleFieldFocus('fixed_price')}
                  onBlur={() => handleFieldBlur('fixed_price')}
                  prefix={currencySymbol}
                  readOnly={!can('edit task')}
                />
              )}

              {!isFixedPrice && (can('view time logs') || can('add time log')) && (
                <Timer
                  mt='xl'
                  task={task}
                />
              )}

              <Checkbox
                label='Billable'
                mt='xl'
                checked={data.billable}
                onChange={event => updateValue('billable', event.currentTarget.checked)}
                disabled={!can('edit task')}
              />

              {!hasRoles(user, ['client']) && (
                <Checkbox
                  label='Hidden from clients'
                  mt='md'
                  checked={data.hidden_from_clients}
                  onChange={event =>
                    updateValue('hidden_from_clients', event.currentTarget.checked)
                  }
                  disabled={!can('edit task')}
                />
              )}

              <MultiSelect
                label='Subscribers'
                placeholder={!data.subscribed_users.length ? 'Select subscribers' : null}
                mt='lg'
                value={data.subscribed_users}
                onChange={values => updateValue('subscribed_users', values)}
                data={usersWithAccessToProject.map(i => ({
                  value: i.id.toString(),
                  label: i.name,
                }))}
                readOnly={!can('edit task')}
              />
            </div>
          </form>
        </>
      ) : (
        <></>
      )}
    </Drawer>
  );
}
