import { Text } from "@mantine/core";
import { openConfirmModal as openMantineConfirmModal } from "@mantine/modals";

export const openConfirmModal = ({
  type = "info",
  title,
  content,
  confirmLabel,
  cancelLabel = "Cancel",
  ...props
}) => {
  const typeColors = {
    info: "blue",
    warning: "orange",
    danger: "red",
  };

  openMantineConfirmModal({
    title: (
      <Text size="xl" fw={700}>
        {title}
      </Text>
    ),
    centered: true,
    overlayProps: { backgroundOpacity: 0.55, blur: 3 },
    children: <Text size="sm">{content}</Text>,
    labels: { confirm: confirmLabel, cancel: cancelLabel },
    confirmProps: { color: typeColors[type] },
    ...props,
  });
};
