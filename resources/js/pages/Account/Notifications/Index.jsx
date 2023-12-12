import EmptyWithIcon from "@/components/EmptyWithIcon";
import Notification from "@/components/Notification";
import useNotificationsStore from "@/hooks/store/useNotificationsStore";
import ContainerBox from "@/layouts/ContainerBox";
import Layout from "@/layouts/MainLayout";
import { day, diffForHumans } from "@/utils/datetime";
import { redirectToUrl } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import { Center, Grid, Group, Stack, Text, Title, UnstyledButton } from "@mantine/core";
import { IconMessage } from "@tabler/icons-react";
import classes from "./css/Index.module.css";

const NotificationsIndex = () => {
  const { groups } = usePage().props;
  const { markAsRead } = useNotificationsStore();
  const dates = Object.keys(groups);

  const open = (notification) => {
    if (notification.read_at === null) markAsRead(notification);
    redirectToUrl(notification.link);
  };

  return (
    <>
      <Grid justify="space-between" align="flex-end" gutter="xl" mb="lg">
        <Grid.Col span="auto">
          <Title order={1}>Notifications</Title>
        </Grid.Col>
        <Grid.Col span="content"></Grid.Col>
      </Grid>

      <ContainerBox maw={550}>
        {dates.length ? (
          <Stack gap={20}>
            {dates.map((date) => (
              <div key={date}>
                <Group justify="space-between" align="center" mb={10}>
                  <Text fz={21} fw={600}>
                    {date}
                  </Text>
                  <div>
                    <Text fz={14} fw={500} align="right">
                      {day(groups[date][0].created_at)}
                    </Text>
                    <Text fz={11} c="dimmed" mt={-2}>
                      {diffForHumans(groups[date][0].created_at)}
                    </Text>
                  </div>
                </Group>
                <Stack gap={14}>
                  {groups[date].map((item) => (
                    <UnstyledButton
                      key={item.id}
                      onClick={() => open(item)}
                      opacity={item.read_at ? 0.5 : 1}
                      className={classes.notification}
                    >
                      <Notification
                        title={item.title}
                        subtitle={item.subtitle}
                        datetime={item.created_at}
                        read={item.read_at !== null}
                      />
                    </UnstyledButton>
                  ))}
                </Stack>
              </div>
            ))}
          </Stack>
        ) : (
          <Center mih={160}>
            <EmptyWithIcon
              title="No notifications"
              subtitle="List of recent notifications"
              icon={IconMessage}
            />
          </Center>
        )}
      </ContainerBox>
    </>
  );
};

NotificationsIndex.layout = (page) => <Layout title="Notifications">{page}</Layout>;

export default NotificationsIndex;
