import { Head } from "@inertiajs/react";
import { useEffect } from "react";
import { useForm } from "laravel-precognition-react-inertia";
import {
  Paper,
  Title,
  Text,
  TextInput,
  Button,
  PasswordInput,
  Alert,
} from "@mantine/core";
import classes from "./css/ResetPassword.module.css";
import GuestLayout from "@/layouts/GuestLayout";

const ResetPassword = ({ token }) => {
  const form = useForm("post", route("auth.newPassword.save"), {
    token,
    email: "",
    password: "",
    password_confirmation: "",
  });

  useEffect(() => {
    return () => {
      form.reset("password", "password_confirmation");
    };
  }, []);

  const submit = (e) => {
    e.preventDefault();
    form.clearErrors();
    form.submit({ preserveScroll: true });
  };

  return (
    <>
      <Title className={classes.title} ta="center">
        Reset Password
      </Title>
      <Text c="dimmed" fz="sm" ta="center">
        Enter your email and new password
      </Text>

      <Paper withBorder shadow="md" p={30} radius="md" mt="xl">
        <form onSubmit={submit}>
          <TextInput
            label="Email"
            placeholder="Your email"
            required
            onChange={(e) => form.setData("email", e.target.value)}
            error={form.errors.email}
          />
          <PasswordInput
            label="Password"
            placeholder="New password"
            required
            mt="md"
            value={form.data.password}
            onChange={(e) => form.setData("password", e.target.value)}
            error={form.errors.password}
          />
          <PasswordInput
            label="Confirm Password"
            placeholder="Repeat new password"
            required
            mt="md"
            value={form.data.password_confirmation}
            onChange={(e) =>
              form.setData("password_confirmation", e.target.value)
            }
            error={form.errors.password_confirmation}
          />
          <Button type="submit" fullWidth mt="xl" disabled={form.processing}>
            Reset password
          </Button>
        </form>
      </Paper>
    </>
  );
};

ResetPassword.layout = (page) => (
  <GuestLayout children={page} title="Reset Password" />
);

export default ResetPassword;
