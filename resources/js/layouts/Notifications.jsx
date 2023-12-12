import EmptyWithIcon from "@/components/EmptyWithIcon";
import Notification from "@/components/Notification";
import useNotificationsStore from "@/hooks/store/useNotificationsStore";
import { redirectTo, redirectToUrl } from "@/utils/route";
import {
  ActionIcon,
  Affix,
  Center,
  Group,
  Indicator,
  Menu,
  Title,
  UnstyledButton,
} from "@mantine/core";
import { IconBellFilled, IconMessage } from "@tabler/icons-react";
import { useEffect, useState } from "react";
import classes from "./css/Notifications.module.css";

export default function Notifications() {
  const { notifications, markAsRead, markAllAsRead } = useNotificationsStore();
  const [unreadCount, setUnreadCount] = useState(0);

  const open = (notification) => {
    if (notification.read_at === null) markAsRead(notification);
    redirectToUrl(notification.link);
  };

  useEffect(() => {
    setUnreadCount(notifications.filter((i) => i.read_at === null).length);
  }, [notifications]);

  return (
    <Affix position={{ top: 20, right: 20 }}>
      <Menu
        withArrow
        position="bottom-end"
        withinPortal
        shadow="md"
        transitionProps={{ duration: 100, transition: "pop-top-right" }}
        offset={{ mainAxis: 10, alignmentAxis: 8 }}
      >
        <Indicator
          color="red"
          disabled={unreadCount === 0}
          label={unreadCount}
          offset={3}
          size={16}
          className={classes.indicator}
        >
          <Menu.Target>
            <ActionIcon radius="xl" size="lg" variant="filled">
              <IconBellFilled style={{ width: "55%", height: "55%" }} />
            </ActionIcon>
          </Menu.Target>

          <Menu.Dropdown miw={340} maw={400} p={12}>
            <Group justify="space-between" m={10} ml={15}>
              <Title order={4}>Notifications</Title>
              {unreadCount > 0 && (
                <UnstyledButton fz={11} onClick={markAllAsRead} className={classes.link}>
                  Mark all as read
                </UnstyledButton>
              )}
            </Group>

            <Menu.Divider />

            {notifications.length ? (
              notifications.map((notification) => (
                <Menu.Item
                  key={notification.id}
                  onClick={() => open(notification)}
                  opacity={notification.read_at ? 0.6 : 1}
                  className={classes.notification}
                >
                  <Notification
                    title={notification.title}
                    subtitle={notification.subtitle}
                    datetime={notification.created_at}
                    read={notification.read_at !== null}
                  />
                </Menu.Item>
              ))
            ) : (
              <Center mih={100}>
                <EmptyWithIcon
                  title="Recent notifications"
                  subtitle="Will be shown here"
                  icon={IconMessage}
                  titleFontSize={17}
                  subtitleFontSize={13}
                  iconSize={38}
                />
              </Center>
            )}

            <Menu.Divider />

            <UnstyledButton
              fz={13}
              fw={500}
              onClick={() => redirectTo("notifications")}
              mx={13}
              my={6}
              className={classes.link}
            >
              View all my notifications
            </UnstyledButton>
          </Menu.Dropdown>
        </Indicator>
      </Menu>
    </Affix>
  );
}
