import GuestLayout from "@/layouts/GuestLayout";
import {
  Button,
  Paper,
  PasswordInput,
  Text,
  TextInput,
  Title,
} from "@mantine/core";
import { useForm } from "laravel-precognition-react-inertia";
import { useEffect } from "react";
import classes from "./css/ResetPassword.module.css";

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
  <GuestLayout title="Reset Password">{page}</GuestLayout>
);

export default ResetPassword;
