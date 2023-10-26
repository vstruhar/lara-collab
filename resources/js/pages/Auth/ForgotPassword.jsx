import { router } from "@inertiajs/react";
import { useForm } from "laravel-precognition-react-inertia";
import {
  Paper,
  Title,
  Text,
  TextInput,
  Button,
  Group,
  Anchor,
  Center,
  Box,
  rem,
  Alert,
} from "@mantine/core";
import { IconArrowLeft, IconInfoCircle } from "@tabler/icons-react";
import classes from "./css/ForgotPassword.module.css";
import GuestLayout from "@/layouts/GuestLayout";

const ForgotPassword = ({ status }) => {
  const form = useForm("post", route("auth.forgotPassword.sendLink"), {
    email: "",
  });

  const submit = (e) => {
    e.preventDefault();
    form.clearErrors();

    form.submit({ preserveScroll: true });
  };

  return (
    <>
      <Title className={classes.title} ta="center">
        Forgot your password?
      </Title>
      <Text c="dimmed" fz="sm" ta="center">
        Enter your email to get a reset link
      </Text>

      <Paper withBorder shadow="md" p={30} radius="md" mt="xl">
        <Text c="dimmed" fz="sm" mb={20}>
          Enter your email and we will email you a password reset link that will
          allow you to choose a new one.
        </Text>

        {status && (
          <Alert radius="md" title={status} icon={<IconInfoCircle />} mb={10}>
            Please read instruction in the email to set a new password for your
            account.
          </Alert>
        )}

        <form onSubmit={submit}>
          <TextInput
            label="Email"
            placeholder="Your email"
            required
            onChange={(e) => form.setData("email", e.target.value)}
            onBlur={() => form.validate("email")}
            error={form.errors.email}
          />
          <Group justify="space-between" mt="lg" className={classes.controls}>
            <Anchor
              c="dimmed"
              size="sm"
              className={classes.control}
              onClick={() => router.get(route("auth.login.form"))}
            >
              <Center inline>
                <IconArrowLeft
                  style={{ width: rem(12), height: rem(12) }}
                  stroke={1.5}
                />
                <Box ml={5}>Back to the login page</Box>
              </Center>
            </Anchor>
            <Button
              type="submit"
              className={classes.control}
              disabled={form.processing}
            >
              Reset password
            </Button>
          </Group>
        </form>
      </Paper>
    </>
  );
};

ForgotPassword.layout = (page) => (
  <GuestLayout children={page} title="Forgot Password" />
);

export default ForgotPassword;
