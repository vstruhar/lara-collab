import ActionButton from "@/components/ActionButton";
import BackButton from "@/components/BackButton";
import useForm from "@/hooks/useForm";
import ContainerBox from "@/layouts/ContainerBox";
import Layout from "@/layouts/MainLayout";
import { redirectTo } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import {
  Anchor,
  Breadcrumbs,
  Chip,
  Fieldset,
  Grid,
  Group,
  TextInput,
  Title,
} from "@mantine/core";

const RoleCreate = () => {
  const { allPermissionsGrouped } = usePage().props;

  const [form, submit, updateValue] = useForm(
    "post",
    route("settings.roles.store"),
    {
      name: "",
      permissions: [],
    },
  );

  const toggle = (permission) => {
    form.data.permissions.includes(permission)
      ? updateValue(
          "permissions",
          form.data.permissions.filter((p) => p !== permission),
        )
      : updateValue("permissions", [...form.data.permissions, permission]);
  };

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <Anchor
          href="#"
          onClick={() => redirectTo("settings.roles.index")}
          fz={14}
        >
          Roles
        </Anchor>
        <div>Create</div>
      </Breadcrumbs>

      <Grid justify="space-between" align="flex-end" gutter="xl" mb="lg">
        <Grid.Col span="auto">
          <Title order={1}>Create role</Title>
        </Grid.Col>
        <Grid.Col span="content"></Grid.Col>
      </Grid>

      <ContainerBox maw={700}>
        <form onSubmit={submit}>
          <TextInput
            label="Name"
            placeholder="Role name"
            required
            value={form.data.name}
            onChange={(e) => updateValue("name", e.target.value)}
            error={form.errors.name}
          />

          <Title order={3} mt="xl">
            Permissions
          </Title>

          {Object.keys(allPermissionsGrouped).map((group) => (
            <Fieldset legend={group} key={group} tt="capitalize" mt="sm">
              <Chip.Group multiple>
                <Group justify="start" gap="sm">
                  {allPermissionsGrouped[group].map((permission) => (
                    <Chip
                      key={permission}
                      radius="sm"
                      checked={form.data.permissions.includes(permission)}
                      onClick={() => toggle(permission)}
                    >
                      {permission}
                    </Chip>
                  ))}
                </Group>
              </Chip.Group>
            </Fieldset>
          ))}

          <Group justify="space-between" mt="xl">
            <BackButton route="settings.roles.index" />
            <ActionButton loading={form.processing}>Create</ActionButton>
          </Group>
        </form>
      </ContainerBox>
    </>
  );
};

RoleCreate.layout = (page) => <Layout title="Create role">{page}</Layout>;

export default RoleCreate;
