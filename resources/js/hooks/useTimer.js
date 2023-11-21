import { humanReadableTime } from "@/utils/timer";
import { useInterval } from "@mantine/hooks";
import dayjs from "dayjs";
import { useEffect, useState } from "react";

export default function useTimer(task) {
  const [timerValue, setTimerValue] = useState("");

  const isTimerRunning = (timeLog) => {
    return timeLog.minutes === null && timeLog.timer_start !== null;
  };

  const [runningTimer, setRunningTimer] = useState(task.time_logs.find((timeLog) => isTimerRunning(timeLog)));

  const timerTick = () => {
    if(runningTimer) {
      const minutes = Math.round(
        (dayjs().unix() - runningTimer.timer_start) / 60,
      );
      setTimerValue(humanReadableTime(minutes));
    }
  };

  const timerInterval = useInterval(timerTick, 1000);

  useEffect(() => {
    const timer = task.time_logs.find((timeLog) =>
      isTimerRunning(timeLog),
    );
    setRunningTimer(timer);

    if (timer) {
      timerTick();
      timerInterval.start();
    } else {
      setTimerValue("");
      timerInterval.stop();
    }
    return timerInterval.stop;
  }, [task.time_logs, runningTimer]);

  return { timerValue, setTimerValue, isTimerRunning, runningTimer };
}
