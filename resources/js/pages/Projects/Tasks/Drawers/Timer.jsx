import useTasksStore from "@/hooks/store/useTasksStore";
import { dateTime } from "@/utils/date";
import { humanReadableTime } from "@/utils/time";
import { Link } from "@inertiajs/react";
import {
  ActionIcon,
  Box,
  Divider,
  Group,
  Stack,
  Text,
  TextInput,
  Tooltip,
  rem,
} from "@mantine/core";
import { IconPlayerPlayFilled, IconPlus } from "@tabler/icons-react";
import { useEffect, useState } from "react";
import classes from "./css/Timer.module.css";

export default function Timer({ task, ...props }) {
  const [timerValue, setTimerValue] = useState("");
  const [totalMinutes, setTotalMinutes] = useState(0);
  const { saveTimeLog } = useTasksStore();

  useEffect(() => {
    setTotalMinutes(task.time_logs.reduce((acc, i) => acc + i.minutes, 0));
    setTimerValue("");
  }, [task.time_logs]);

  const isValid = (value) => {
    // examples of valid values: 1:00, 10:30, 28:59, 1,5, 2.5
    return /^(\d{1,2}:[0-5]{1}[0-9]{1})$|^(\d{1,3}\.\d{0,2})$|^(\d{1,3},\d{0,2})$|^(\d{1,3})$/.test(
      value,
    );
  };

  return (
    <Box className={classes.container} {...props}>
      <Stack>
        <Group className={classes.timer} justify="space-between" wrap="nowrap">
          <ActionIcon size={32} radius="xl" variant="filled">
            <IconPlayerPlayFilled style={{ width: rem(16), height: rem(16) }} />
          </ActionIcon>

          <Group wrap="nowrap" gap={5}>
            <TextInput
              variant="unstyled"
              size="sm"
              placeholder="0:00"
              className={`${classes.input} ${
                isValid(timerValue)
                  ? null
                  : timerValue.length && classes.invalid
              }`}
              value={timerValue}
              onChange={(event) => {
                if (/^(\d|\.|,|:)*$/.test(event.currentTarget.value)) {
                  setTimerValue(event.currentTarget.value);
                }
              }}
            />
          </Group>

          <ActionIcon
            size={32}
            radius="xl"
            variant="filled"
            onClick={() => saveTimeLog(task, timerValue)}
            disabled={!isValid(timerValue)}
          >
            <IconPlus style={{ width: rem(16), height: rem(16) }} stroke={3} />
          </ActionIcon>
        </Group>
        {task.time_logs.length > 0 && (
          <>
            <Stack className={classes.list} gap={5}>
              {task.time_logs.map((timeLog) => (
                <Group key={timeLog.id} wrap="nowrap" justify="space-between">
                  <Text fz={14}>
                    <Link href={route("users.edit", timeLog.user_id)}>
                      {timeLog.user.name}
                    </Link>
                  </Text>
                  <Tooltip
                    label={dateTime(timeLog.created_at)}
                    openDelay={250}
                    withArrow
                  >
                    <Text fz={14} c="dimmed">
                      {humanReadableTime(timeLog.minutes)}h
                    </Text>
                  </Tooltip>
                </Group>
              ))}
            </Stack>
            <Divider />
            <Group
              wrap="nowrap"
              justify="space-between"
              className={classes.summary}
            >
              <Text>Total:</Text>
              <Text fw={600}>{humanReadableTime(totalMinutes)}h</Text>
            </Group>
          </>
        )}
      </Stack>
    </Box>
  );
}
