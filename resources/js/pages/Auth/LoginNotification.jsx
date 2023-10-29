import { Alert } from "@mantine/core";
import {
  IconInfoCircle,
  IconAlertTriangle,
  IconExclamationCircle,
} from "@tabler/icons-react";

export default function LoginNotification({ notify }) {
  return (
    <div style={{ marginTop: "25px" }}>
      {notify === "password-reset" && (
        <Alert radius="md" title="Password was reset" icon={<IconInfoCircle />}>
          Your password was successfully updated, you may use it to login.
        </Alert>
      )}
      {notify === "social-login-user-not-found" && (
        <Alert
          radius="md"
          title="Login failed"
          icon={<IconAlertTriangle />}
          color="orange"
        >
          No user was found with your Google email address.
        </Alert>
      )}
      {notify === "social-login-failed" && (
        <Alert
          radius="md"
          title="Whoops, something went wrong"
          icon={<IconExclamationCircle />}
          color="red"
        >
          Unexpected error has occurred, please try logging in with your email
          and password.
        </Alert>
      )}
    </div>
  );
}
