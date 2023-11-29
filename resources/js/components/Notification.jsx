import { dateTime } from "@/utils/datetime";
import { Group, Text, rem } from "@mantine/core";
import { IconMessage } from "@tabler/icons-react";
import classes from "./css/Notification.module.css";

export default function Notification({ title, subtitle, datetime, read }) {
  return (
    <Group wrap="nowrap">
      <IconMessage
        style={{ width: rem(30), height: rem(30), flexShrink: 0 }}
        className={read ? null : classes.icon}
      />
      <div>
        <Text fz={13} lh={rem(16)}>
          {title}
        </Text>
        <Text fz={11} c="dimmed">
          {`${subtitle}, ${dateTime(datetime)}`}
        </Text>
      </div>
    </Group>
  );
}
