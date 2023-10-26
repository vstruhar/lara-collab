import { router } from "@inertiajs/react";
import { useForm } from "laravel-precognition-react-inertia";
import {
  TextInput,
  PasswordInput,
  Checkbox,
  Anchor,
  Paper,
  Title,
  Text,
  Group,
  Divider,
  Button,
  Alert,
} from "@mantine/core";
import { IconInfoCircle } from "@tabler/icons-react";
import classes from "./css/Login.module.css";
import GuestLayout from "@/layouts/GuestLayout";
import GoogleIcon from "@/icons/GoogleIcon";

const Login = ({ notify }) => {
  const form = useForm("post", route("auth.login.attempt"), {
    email: "",
    password: "",
    remember: false,
  });

  const submit = (e) => {
    e.preventDefault();

    form.submit({
      preserveScroll: true,
      onSuccess: () => form.reset(),
    });
  };

  return (
    <>
      <Title ta="center" className={classes.title}>
        Welcome back!
      </Title>
      <Text c="dimmed" size="sm" ta="center" mt={5}>
        You may login to your account below
      </Text>

      <form onSubmit={submit}>
        <Paper withBorder shadow="md" p={30} mt={30} radius="md">
          {notify === "password-reset" && (
            <Alert
              radius="md"
              title="Password was reset"
              icon={<IconInfoCircle />}
              mb={16}
            >
              Your password was successfully updated, you may use it to login.
            </Alert>
          )}

          <Group grow mb="md" mt="md">
            <Button leftSection={<GoogleIcon />} variant="default" radius="xl">
              Google
            </Button>
          </Group>

          <Divider
            label="Or continue with email"
            labelPosition="center"
            my="lg"
          />

          <TextInput
            label="Email"
            placeholder="Your email"
            required
            value={form.data.email}
            onChange={(e) => form.setData("email", e.target.value)}
            onBlur={() => form.validate("email")}
            error={form.errors.email}
          />
          <PasswordInput
            label="Password"
            placeholder="Your password"
            required
            mt="md"
            value={form.data.password}
            onChange={(e) => form.setData("password", e.target.value)}
          />
          <Group justify="space-between" mt="lg">
            <Checkbox label="Remember me" />
            <Anchor
              type="button"
              size="sm"
              onClick={() => router.get(route("auth.forgotPassword.form"))}
            >
              Forgot password?
            </Anchor>
          </Group>
          <Button type="submit" fullWidth mt="xl" disabled={form.processing}>
            Sign in
          </Button>
        </Paper>
      </form>
    </>
  );
};

Login.layout = (page) => <GuestLayout children={page} title="Login" />;

export default Login;
