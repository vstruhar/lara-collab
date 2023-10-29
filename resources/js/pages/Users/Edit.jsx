import ActionButton from "@/components/ActionButton";
import BackButton from "@/components/BackButton";
import Layout from "@/layouts/MainLayout";
import RoleService from "@/services/RoleService";
import UserService from "@/services/UserService";
import { redirectTo } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import {
  Anchor,
  Avatar,
  Breadcrumbs,
  Divider,
  FileInput,
  Grid,
  Group,
  MultiSelect,
  NumberInput,
  Paper,
  PasswordInput,
  Text,
  TextInput,
  Title,
} from "@mantine/core";
import { useScrollIntoView } from "@mantine/hooks";
import { useForm } from "laravel-precognition-react-inertia";

const UsersEdit = () => {
  const { user } = usePage().props;
  const roleService = new RoleService();
  const { scrollIntoView } = useScrollIntoView({ duration: 1000 });

  const form = useForm("post", route("users.update", user.id), {
    _method: "put",
    avatar: null,
    job_title: user.job_title,
    name: user.name,
    phone: user.phone,
    rate: user.rate / 100,
    email: user.email,
    password: "",
    password_confirmation: "",
    roles: user.roles,
  });

  const submit = (e) => {
    e.preventDefault();

    form.submit({
      forceFormData: true,
      preserveScroll: true,
      onError: () => {
        scrollIntoView({
          target: document.querySelector('[data-error="true"]'),
        });
      },
    });
  };

  const updateValue = (field, value) => {
    form.setData(field, value);
    form.forgetError(field);
  };

  return (
    <>
      <Breadcrumbs fz={14} mb={30}>
        <Anchor href="#" onClick={redirectTo("users.index")} fz={14}>
          Users
        </Anchor>
        <div>Edit</div>
      </Breadcrumbs>

      <Grid justify="space-between" align="flex-end" gutter="xl" mb="lg">
        <Grid.Col span="auto">
          <Title order={1}>Edit user</Title>
        </Grid.Col>
        <Grid.Col span="content"></Grid.Col>
      </Grid>

      <Paper px={45} py={35} withBorder maw={550}>
        <form onSubmit={submit}>
          <Grid justify="flex-start" align="flex-start" gutter="lg">
            <Grid.Col span="content">
              <Avatar
                src={
                  user.avatar !== null && form.data.avatar === null
                    ? user.avatar
                    : URL.createObjectURL(form.data.avatar)
                }
                size={120}
              >
                {new UserService(form.data).getInitials()}
              </Avatar>
            </Grid.Col>
            <Grid.Col span="auto">
              <FileInput
                label="Profile image"
                placeholder="Choose image"
                accept="image/png,image/jpeg"
                onChange={(image) => updateValue("avatar", image)}
                clearable
                error={form.errors.avatar}
              />
              <Text size="xs" c="dimmed" mt="sm">
                If no image is uploaded we will try to fetch it via{" "}
                <Anchor
                  href="https://unavatar.io"
                  target="_blank"
                  opacity={0.6}
                >
                  unavatar.io
                </Anchor>{" "}
                service.
              </Text>
            </Grid.Col>
          </Grid>

          <TextInput
            label="Name"
            placeholder="User full name"
            required
            mt="md"
            value={form.data.name}
            onChange={(e) => updateValue("name", e.target.value)}
            error={form.errors.name}
          />

          <TextInput
            label="Job title"
            placeholder="e.g. Frontend Developer"
            required
            mt="md"
            value={form.data.job_title}
            onChange={(e) => updateValue("job_title", e.target.value)}
            error={form.errors.job_title}
          />

          <MultiSelect
            label="Roles"
            placeholder="Select role"
            required
            mt="md"
            value={form.data.roles}
            onChange={(values) => updateValue("roles", values)}
            data={roleService.getSelectValues()}
            error={form.errors.roles}
          />

          <Group grow mt="md">
            <TextInput
              label="Phone"
              placeholder="Users phone number"
              value={form.data.phone}
              onChange={(e) => updateValue("phone", e.target.value)}
              error={form.errors.phone}
            />

            <NumberInput
              label="Hourly rate"
              allowNegative={false}
              clampBehavior="strict"
              decimalScale={2}
              fixedDecimalScale={true}
              prefix="$"
              value={form.data.rate}
              onChange={(value) => updateValue("rate", value)}
              error={form.errors.rate}
            />
          </Group>

          <Divider
            mt="xl"
            mb="md"
            label="Login credentials"
            labelPosition="center"
          />

          <TextInput
            label="Email"
            placeholder="User email"
            required
            value={form.data.email}
            onChange={(e) => updateValue("email", e.target.value)}
            onBlur={() => form.validate("email")}
            error={form.errors.email}
          />

          <PasswordInput
            label="Password"
            placeholder="User password"
            mt="md"
            value={form.data.password}
            onChange={(e) => updateValue("password", e.target.value)}
            error={form.errors.password}
          />

          <PasswordInput
            label="Confirm password"
            placeholder="Confirm password"
            mt="md"
            value={form.data.password_confirmation}
            onChange={(e) =>
              updateValue("password_confirmation", e.target.value)
            }
            error={form.errors.password_confirmation}
          />

          <Group justify="space-between" mt="xl">
            <BackButton route="users.index" />
            <ActionButton loading={form.processing}>Update</ActionButton>
          </Group>
        </form>
      </Paper>
    </>
  );
};

UsersEdit.layout = (page) => <Layout children={page} title="Edit user" />;

export default UsersEdit;
