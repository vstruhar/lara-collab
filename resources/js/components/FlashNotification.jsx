import { usePage } from "@inertiajs/react";
import { Alert, Box, Transition, rem } from "@mantine/core";
import {
  IconAlertCircle,
  IconCircleCheck,
  IconCircleX,
  IconInfoCircle,
} from "@tabler/icons-react";
import { useEffect, useState } from "react";
import classes from "./css/FlashNotification.module.css";

const types = {
  info: {
    color: "blue",
    timeout: 10000,
    icon: (
      <IconInfoCircle style={{ width: rem(50), height: rem(50) }} stroke={2} />
    ),
  },
  success: {
    color: "green",
    timeout: 5000,
    icon: (
      <IconCircleCheck style={{ width: rem(50), height: rem(50) }} stroke={2} />
    ),
  },
  warning: {
    color: "yellow",
    timeout: 10000,
    icon: (
      <IconAlertCircle style={{ width: rem(50), height: rem(50) }} stroke={2} />
    ),
  },
  error: {
    color: "red",
    timeout: 10000,
    icon: (
      <IconCircleX style={{ width: rem(50), height: rem(50) }} stroke={2} />
    ),
  },
};

export default function FlashNotification() {
  const [visible, setVisible] = useState(false);
  const { flash } = usePage().props;

  useEffect(() => {
    setVisible(true);

    const timeoutId = setTimeout(
      () => setVisible(false),
      types[flash?.type]?.timeout,
    );
    return () => clearTimeout(timeoutId);
  }, [flash]);

  return (
    <Transition
      mounted={visible}
      transition="slide-down"
      duration={300}
      exitDuration={600}
      timingFunction="easeOut"
    >
      {(styles) => (
        <Box mb="lg" style={styles} className={classes.container}>
          {flash && (
            <Alert
              variant="filled"
              color={types[flash.type].color}
              title={flash.title}
              icon={types[flash.type].icon}
              classNames={{
                icon: classes.icon,
                title: classes.title,
                label: classes.label,
                message: classes.message,
              }}
              radius="md"
              withCloseButton
              onClose={() => setVisible(false)}
            >
              {flash.message}
            </Alert>
          )}
        </Box>
      )}
    </Transition>
  );
}
