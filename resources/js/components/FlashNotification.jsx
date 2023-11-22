import { usePage } from "@inertiajs/react";
import { Alert, Box, Transition, rem } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import {
  IconAlertCircle,
  IconCircleCheck,
  IconCircleX,
  IconInfoCircle,
} from "@tabler/icons-react";
import { useEffect } from "react";
import classes from "./css/FlashNotification.module.css";

const iconProps = { style: { width: rem(50), height: rem(50) }, stroke: 2 };

const types = {
  info: {
    color: "blue",
    timeout: 8000,
    icon: <IconInfoCircle {...iconProps} />,
  },
  success: {
    color: "green",
    timeout: 4000,
    icon: <IconCircleCheck {...iconProps} />,
  },
  warning: {
    color: "yellow",
    timeout: 10000,
    icon: <IconAlertCircle {...iconProps} />,
  },
  error: {
    color: "red",
    timeout: 10000,
    icon: <IconCircleX {...iconProps} />,
  },
};

export default function FlashNotification() {
  const [opened, { open, close }] = useDisclosure(false);
  const { flash } = usePage().props;

  useEffect(() => {
    open();

    const timeoutId = setTimeout(() => close(), types[flash?.type]?.timeout);
    return () => clearTimeout(timeoutId);
  }, [flash]);

  const customSlideDown = {
    in: { opacity: 1, transform: "translate(-50%, 0)" },
    out: { opacity: 0, transform: "translate(-50%, -100%)" },
    common: { transformOrigin: "top" },
    transitionProperty: "transform, opacity",
  };

  return (
    <Transition
      mounted={opened}
      transition={customSlideDown}
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
              onClose={close}
            >
              {flash.message}
            </Alert>
          )}
        </Box>
      )}
    </Transition>
  );
}
