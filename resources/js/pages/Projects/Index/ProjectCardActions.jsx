import { openConfirmModal } from "@/components/ConfirmModal";
import useForm from "@/hooks/useForm";
import { router } from "@inertiajs/react";
import { ActionIcon, Menu, rem } from "@mantine/core";
import {
  IconArchive,
  IconArchiveOff,
  IconDots,
  IconPencil,
  IconUsers,
} from "@tabler/icons-react";
import UserAccessModal from "./UserAccessModal.jsx";

export default function ProjectCardActions({ item }) {
  const [archiveForm] = useForm("delete", route("projects.destroy", item.id));
  const [restoreForm] = useForm("post", route("projects.restore", item.id));

  const openArchiveModal = () =>
    openConfirmModal({
      type: "danger",
      title: "Archive project",
      content: `Are you sure you want to archive this project? This action will prevent users from accessing it.`,
      confirmLabel: "Archive",
      confirmProps: { color: "red" },
      action: () => archiveForm.submit(),
    });

  const openRestoreModal = () =>
    openConfirmModal({
      type: "info",
      title: "Restore project",
      content: `Are you sure you want to restore this project?`,
      confirmLabel: "Restore",
      confirmProps: { color: "blue" },
      action: () => restoreForm.submit(),
    });

  const openUserAccess = () => UserAccessModal(item);

  return (
    <Menu
      withArrow
      position="bottom-end"
      withinPortal
      transitionProps={{ duration: 100, transition: "pop-top-right" }}
      offset={{ mainAxis: 3, alignmentAxis: 5 }}
    >
      <Menu.Target>
        <ActionIcon variant="subtle" color="gray">
          <IconDots style={{ width: rem(20), height: rem(20) }} stroke={1.5} />
        </ActionIcon>
      </Menu.Target>
      <Menu.Dropdown>
        {can("assign users to project") && (
          <Menu.Item
            leftSection={
              <IconUsers
                style={{ width: rem(16), height: rem(16) }}
                stroke={1.5}
              />
            }
            onClick={openUserAccess}
          >
            User access
          </Menu.Item>
        )}
        {can("edit project") && (
          <Menu.Item
            leftSection={
              <IconPencil
                style={{ width: rem(16), height: rem(16) }}
                stroke={1.5}
              />
            }
            onClick={() => router.visit(route("projects.edit", item.id))}
          >
            Edit
          </Menu.Item>
        )}
        {can("restore project") && route().params.archived && (
          <Menu.Item
            leftSection={
              <IconArchiveOff
                style={{ width: rem(16), height: rem(16) }}
                stroke={1.5}
              />
            }
            color="blue"
            onClick={openRestoreModal}
          >
            Restore
          </Menu.Item>
        )}
        {can("archive project") && !route().params.archived && (
          <Menu.Item
            leftSection={
              <IconArchive
                style={{ width: rem(16), height: rem(16) }}
                stroke={1.5}
              />
            }
            color="red"
            onClick={openArchiveModal}
          >
            Archive
          </Menu.Item>
        )}
      </Menu.Dropdown>
    </Menu>
  );
}
