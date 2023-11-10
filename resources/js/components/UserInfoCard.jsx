import { getInitials } from "@/utils/user";
import { Avatar, Grid, Group, Paper, Text } from "@mantine/core";
import { IconAt, IconPhoneCall } from "@tabler/icons-react";
import classes from "./css/UserInfoCard.module.css";

export function UserInfoCard({ user }) {
  return (
    <Paper p={25} withBorder maw={400}>
      <Grid justify="flex-start" align="flex-start">
        <Grid.Col span="content">
          <Avatar src={user.avatar} size={94}>
            {getInitials(user.name)}
          </Avatar>
        </Grid.Col>
        <Grid.Col span="auto">
          <Text fz="xs" tt="uppercase" fw={700} c="dimmed">
            {user.job_title || "Job title"}
          </Text>

          <Text fz="lg" fw={500} className={classes.name}>
            {user.name || "Name"}
          </Text>

          <Group wrap="nowrap" gap={10} mt={3}>
            <IconAt stroke={1.5} size="1rem" className={classes.icon} />
            <Text fz="xs" c="dimmed">
              {user.email || "user@mail.com"}
            </Text>
          </Group>

          <Group wrap="nowrap" gap={10} mt={5}>
            <IconPhoneCall stroke={1.5} size="1rem" className={classes.icon} />
            <Text fz="xs" c="dimmed">
              {user.phone || "+123 111 222"}
            </Text>
          </Group>
        </Grid.Col>
      </Grid>
    </Paper>
  );
}
