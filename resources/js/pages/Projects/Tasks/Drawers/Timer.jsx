import useTasksStore from "@/hooks/store/useTasksStore";
import useTimer from "@/hooks/useTimer";
import { dateTime } from "@/utils/datetime";
import { humanReadableTime, isTimeValueValid } from "@/utils/timer";
import { Link, usePage } from "@inertiajs/react";
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
import { IconPlayerPlayFilled, IconPlayerStopFilled, IconPlus, IconX } from "@tabler/icons-react";
import { useEffect, useState } from "react";
import classes from "./css/Timer.module.css";

export default function Timer({ task, ...props }) {
  const {
    auth: { user },
  } = usePage().props;
  const [totalMinutes, setTotalMinutes] = useState(0);
  const { timerValue, setTimerValue, isTimerRunning, runningTimer } = useTimer(task);
  const { saveTimeLog, deleteTimerLog, startTimer, stopTimer } = useTasksStore();

  useEffect(() => {
    setTotalMinutes(task.time_logs.reduce((acc, i) => acc + i.minutes, 0));
    setTimerValue("");
  }, [task.time_logs]);

  return (
    <Box className={classes.container} {...props}>
      <Stack>
        {can("add time log") ? (
          <Group className={classes.timer} justify="space-between" wrap="nowrap">
            {runningTimer ? (
              <ActionIcon
                size={32}
                radius="xl"
                variant="filled"
                onClick={() => stopTimer(task, runningTimer.id)}
              >
                <IconPlayerStopFilled style={{ width: rem(16), height: rem(16) }} />
              </ActionIcon>
            ) : (
              <ActionIcon size={32} radius="xl" variant="filled" onClick={() => startTimer(task)}>
                <IconPlayerPlayFilled style={{ width: rem(16), height: rem(16) }} />
              </ActionIcon>
            )}

            <Group wrap="nowrap" gap={5}>
              <TextInput
                variant="unstyled"
                size="sm"
                placeholder="0:00"
                readOnly={runningTimer !== undefined}
                className={`
                  ${classes.input}
                  ${runningTimer ? classes.blink : null}
                  ${isTimeValueValid(timerValue) ? null : timerValue.length && classes.invalid}
                `}
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
              disabled={!isTimeValueValid(timerValue) || runningTimer}
            >
              <IconPlus style={{ width: rem(16), height: rem(16) }} stroke={3} />
            </ActionIcon>
          </Group>
        ) : (
          <Text ml={10} fz={15} fw={500}>
            Time logs
          </Text>
        )}
        {task.time_logs.length > 0 && can("view time logs") && (
          <>
            <Stack className={classes.list} gap={5}>
              {task.time_logs.map((timeLog) => (
                <Group
                  key={timeLog.id}
                  wrap="nowrap"
                  justify="space-between"
                  className={classes.row}
                >
                  <Text fz={14}>
                    <Link href={route("users.edit", timeLog.user_id)}>{timeLog.user.name}</Link>
                  </Text>
                  {isTimerRunning(timeLog) ? (
                    <Text fz={14} fw={600} c="blue" className={classes.blink}>
                      running
                    </Text>
                  ) : (
                    <Group gap={7}>
                      {can("delete time log") && timeLog.user_id === user.id && (
                        <IconX
                          className={classes.delete}
                          stroke={1.5}
                          onClick={() => deleteTimerLog(task, timeLog.id)}
                        />
                      )}
                      <Tooltip label={dateTime(timeLog.created_at)} openDelay={250} withArrow>
                        <Text fz={14} c="dimmed">
                          {humanReadableTime(timeLog.minutes)}h
                        </Text>
                      </Tooltip>
                    </Group>
                  )}
                </Group>
              ))}
            </Stack>
            <Divider my={-5} />
            <Group wrap="nowrap" justify="space-between" className={classes.summary}>
              <Text fz={15} fw={500}>
                Total:
              </Text>
              <Text fw={600}>{humanReadableTime(totalMinutes)}h</Text>
            </Group>
          </>
        )}
      </Stack>
    </Box>
  );
}
