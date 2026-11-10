import ActionButton from '@/components/ActionButton';
import BackButton from '@/components/BackButton';
import useForm from '@/hooks/useForm';
import ContainerBox from '@/layouts/ContainerBox';
import Layout from '@/layouts/MainLayout';
import { redirectTo } from '@/utils/route';
import { usePage } from '@inertiajs/react';
import { Anchor, Breadcrumbs, Chip, Fieldset, Grid, Group, TextInput, Title } from '@mantine/core';

const RoleEdit = () => {
  const { item, allPermissionsGrouped } = usePage().props;

  const [form, submit, updateValue] = useForm('post', route('settings.roles.update', item.id), {
    _method: 'put',
    name: item.name,
    permissions: item.permissions,
  });

  return (
    <>
      <Breadcrumbs
        fz={14}
        mb={30}
      >
        <Anchor
          href='#'
          onClick={() => redirectTo('settings.roles.index')}
          fz={14}
        >
          Roles
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
          <Title order={1}>Edit role</Title>
        </Grid.Col>
        <Grid.Col span='content'></Grid.Col>
      </Grid>

      <ContainerBox maw={700}>
        <form onSubmit={submit}>
          {form.data.name !== 'client' && (
            <TextInput
              label='Name'
              placeholder='Role name'
              required
              value={form.data.name}
              onChange={e => updateValue('name', e.target.value)}
              error={form.errors.name}
            />
          )}

          <Title
            order={3}
            mt={form.data.name !== 'client' ? 'xl' : ''}
          >
            Permissions
          </Title>

          {Object.keys(allPermissionsGrouped).map(group => (
            <Fieldset
              legend={group}
              key={group}
              tt='capitalize'
              mt='sm'
            >
              <Chip.Group
                multiple
                value={form.data.permissions}
                onChange={permissions => updateValue('permissions', permissions)}
              >
                <Group
                  justify='start'
                  gap='sm'
                >
                  {allPermissionsGrouped[group].map(permission => (
                    <Chip
                      key={permission}
                      value={permission}
                      radius='sm'
                    >
                      {permission}
                    </Chip>
                  ))}
                </Group>
              </Chip.Group>
            </Fieldset>
          ))}

          <Group
            justify='space-between'
            mt='xl'
          >
            <BackButton route='settings.roles.index' />
            <ActionButton loading={form.processing}>Update</ActionButton>
          </Group>
        </form>
      </ContainerBox>
    </>
  );
};

RoleEdit.layout = page => <Layout title='Edit role'>{page}</Layout>;

export default RoleEdit;
