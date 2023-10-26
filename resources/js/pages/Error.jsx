import { Container, Title, Text, Button, Group } from "@mantine/core";
import classes from "./css/Error.module.css";
import { Head, router } from "@inertiajs/react";

export default function Error({ status }) {
  const title = {
    503: "Service Unavailable",
    500: "Server Error",
    404: "Page Not Found",
    403: "Forbidden",
  }[status];

  const description = {
    503: "Sorry, we are doing some maintenance. Please check back soon.",
    500: "Whoops, something went wrong on our servers.",
    404: "Sorry, the page you are looking for could not be found.",
    403: "Sorry, you are forbidden from accessing this page.",
  }[status];

  return (
    <>
      <Head title={title} />
      <Container className={classes.root}>
        <div className={classes.inner}>
          <div className={classes.image}>{status}</div>
          <div className={classes.content}>
            <Title className={classes.title}>{title}</Title>
            <Text
              c="dimmed"
              size="lg"
              ta="center"
              className={classes.description}
            >
              {description}
            </Text>
            <Group justify="center">
              <Button size="md" onClick={() => router.get(route("dashboard"))}>
                Take me back to home page
              </Button>
            </Group>
          </div>
        </div>
      </Container>
    </>
  );
}
