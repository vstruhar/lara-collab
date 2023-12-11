import { redirectTo } from "@/utils/route";
import { ActionIcon, Group, Menu, rem } from "@mantine/core";
import { IconArchive, IconArchiveOff, IconDots, IconPencil } from "@tabler/icons-react";
import { useForm } from "laravel-precognition-react-inertia";
import { openConfirmModal } from "./ConfirmModal";

export default function TableRowActions({
  item,
  editRoute,
  editPermission,
  archivePermission,
  restorePermission,
  archive,
  restore,
  children,
}) {
  const archiveForm = useForm("delete", route(archive.route, item.id));
  const restoreForm = useForm("post", route(restore.route, item.id));

  const openArchiveModal = () =>
    openConfirmModal({
      type: "danger",
      title: archive.title,
      content: archive.content,
      confirmLabel: archive.confirmLabel,
      confirmProps: { color: "red" },
      onConfirm: () => archiveForm.submit(),
    });

  const openRestoreModal = () =>
    openConfirmModal({
      type: "info",
      title: restore.title,
      content: restore.content,
      confirmLabel: restore.confirmLabel,
      confirmProps: { color: "blue" },
      onConfirm: () => restoreForm.submit(),
    });

  return (
    <Group gap={0} justify="flex-end" wrap="nowrap">
      {children}
      {can(editPermission) && !route().params.archived && (
        <ActionIcon variant="subtle" color="blue" onClick={() => redirectTo(editRoute, item.id)}>
          <IconPencil style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
        </ActionIcon>
      )}
      {((can(archivePermission) && !route().params.archived) ||
        (can(restorePermission) && route().params.archived)) &&
        item.name !== "client" && (
          <Menu
            withArrow
            position="bottom-end"
            withinPortal
            shadow="md"
            transitionProps={{ duration: 100, transition: "pop-top-right" }}
            offset={{ mainAxis: 3, alignmentAxis: 5 }}
          >
            <Menu.Target>
              <ActionIcon variant="subtle" color="gray">
                <IconDots style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
              </ActionIcon>
            </Menu.Target>
            <Menu.Dropdown>
              {can(restorePermission) && route().params.archived && (
                <Menu.Item
                  leftSection={
                    <IconArchiveOff style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
                  }
                  color="blue"
                  onClick={openRestoreModal}
                >
                  Restore
                </Menu.Item>
              )}
              {can(archivePermission) && !route().params.archived && (
                <Menu.Item
                  leftSection={
                    <IconArchive style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
                  }
                  color="red"
                  onClick={openArchiveModal}
                >
                  Archive
                </Menu.Item>
              )}
            </Menu.Dropdown>
          </Menu>
        )}
    </Group>
  );
}
