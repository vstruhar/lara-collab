import ActionButton from '@/components/ActionButton';
import BackButton from '@/components/BackButton';
import useForm from '@/hooks/useForm';
import ContainerBox from '@/layouts/ContainerBox';
import Layout from '@/layouts/MainLayout';
import { redirectTo } from '@/utils/route';
import { usePage } from '@inertiajs/react';
import {
  Anchor,
  Breadcrumbs,
  ColorInput,
  Grid,
  Group,
  NumberInput,
  TextInput,
  Title,
} from '@mantine/core';

const PriorityEdit = () => {
  const { item } = usePage().props;

  const [form, submit, updateValue] = useForm(
    'post',
    route('settings.task-priorities.update', item.id),
    {
      _method: 'put',
      label: item.label,
      color: item.color || '',
      order: item.order,
    }
  );

  return (
    <>
      <Breadcrumbs
        fz={14}
        mb={30}
      >
        <Anchor
          href='#'
          onClick={() => redirectTo('settings.task-priorities.index')}
          fz={14}
        >
          Priorities
        </Anchor>
        <div>Edit</div>
      </Breadcrumbs>

      <Grid
        justify='space-between'
        align='flex-end'
        gap='xl'
        mb='lg'
      >
        <Grid.Col span='auto'>
          <Title order={1}>Edit priority</Title>
        </Grid.Col>
        <Grid.Col span='content'></Grid.Col>
      </Grid>

      <ContainerBox maw={400}>
        <form onSubmit={submit}>
          <TextInput
            label='Label'
            placeholder='Priority label'
            required
            value={form.data.label}
            onChange={e => updateValue('label', e.target.value)}
            error={form.errors.label}
          />
          <ColorInput
            label='Color'
            placeholder='Priority color'
            required
            mt='md'
            swatches={[
              '#343A40',
              '#E03231',
              '#C2255C',
              '#9C36B5',
              '#6741D9',
              '#3B5BDB',
              '#2771C2',
              '#2A8599',
              '#2B9267',
              '#309E44',
              '#66A810',
              '#F08C00',
              '#E7590D',
            ]}
            swatchesPerRow={7}
            value={form.data.color}
            onChange={color => updateValue('color', color)}
            error={form.errors.color}
          />
          <NumberInput
            label='Order'
            placeholder='Display order (e.g., 1, 2, 3)'
            required
            mt='md'
            min={1}
            value={form.data.order}
            onChange={value => updateValue('order', value)}
            error={form.errors.order}
          />

          <Group
            justify='space-between'
            mt='xl'
          >
            <BackButton route='settings.task-priorities.index' />
            <ActionButton loading={form.processing}>Update</ActionButton>
          </Group>
        </form>
      </ContainerBox>
    </>
  );
};

PriorityEdit.layout = page => <Layout title='Edit priority'>{page}</Layout>;

export default PriorityEdit;
