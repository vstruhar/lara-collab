import useNotificationsStore from "@/hooks/store/useNotificationsStore";
import { dateTime } from "@/utils/datetime";
import { getMessage } from "@/utils/notification";
import { redirectToUrl } from "@/utils/route";
import {
  ActionIcon,
  Affix,
  Flex,
  Group,
  Indicator,
  Menu,
  Text,
  UnstyledButton,
  rem,
} from "@mantine/core";
import { IconBellFilled, IconMessage } from "@tabler/icons-react";
import { useEffect, useState } from "react";
import classes from "./css/Notifications.module.css";

export default function Notifications() {
  const { notifications, markAsRead, markAllAsRead } = useNotificationsStore();
  const [unreadCount, setUnreadCount] = useState(0);

  const open = (notification) => {
    if (notification.read_at === null) markAsRead(notification);
    redirectToUrl(notification.payload.link);
  };

  useEffect(() => {
    setUnreadCount(notifications.filter((i) => i.read_at === null).length);
  }, [notifications]);

  const getText = (notification) => {
    return getMessage(notification).title;
  };

  return (
    <Affix position={{ top: 20, right: 20 }}>
      <Menu
        withArrow
        position="bottom-end"
        withinPortal
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

          <Menu.Dropdown miw={340}>
            <Menu.Label>
              <Group justify="space-between">
                <Text c="white" fz="md" fw={600}>
                  Notifications
                </Text>
                <UnstyledButton fz={11} c="dimmed" onClick={markAllAsRead}>
                  Mark all as read
                </UnstyledButton>
              </Group>
            </Menu.Label>

            <Menu.Divider />
            {notifications.length ? (
              notifications.map((notification) => (
                <Menu.Item
                  key={notification.id}
                  onClick={() => open(notification)}
                  opacity={notification.read_at ? 0.4 : 1}
                >
                  <Group>
                    <IconMessage
                      style={{ width: rem(30), height: rem(30) }}
                      className={notification.read_at ? null : classes.icon}
                    />
                    <div>
                      <Text fz={13}>{getText(notification)}</Text>
                      <Text fz={11} c="dimmed">
                        {`${notification.payload.project.name} ${dateTime(
                          notification.created_at,
                        )}`}
                      </Text>
                    </div>
                  </Group>
                </Menu.Item>
              ))
            ) : (
              <Flex
                mih={100}
                gap={2}
                justify="center"
                align="center"
                direction="column"
                wrap="nowrap"
              >
                <Text size="lg" fw={600} ta="center" c="dimmed">
                  All caught up!
                </Text>
                <Text fz={13} ta="center" c="dimmed" opacity={0.5}>
                  No pending notifications
                </Text>
              </Flex>
            )}
          </Menu.Dropdown>
        </Indicator>
      </Menu>
    </Affix>
  );
}
