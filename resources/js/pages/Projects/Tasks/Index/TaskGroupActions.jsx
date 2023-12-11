import { openConfirmModal } from "@/components/ConfirmModal";
import { ActionIcon, Group, Menu, rem } from "@mantine/core";
import { IconArchive, IconArchiveOff, IconDots, IconPencil } from "@tabler/icons-react";
import { useForm } from "laravel-precognition-react-inertia";
import EditTasksGroupModal from "./Modals/EditTasksGroupModal";

export default function TaskGroupActions({ group, ...props }) {
  const archiveForm = useForm(
    "delete",
    route("projects.task-groups.destroy", [group.project_id, group.id]),
  );
  const restoreForm = useForm(
    "post",
    route("projects.task-groups.restore", [group.project_id, group.id]),
  );

  const openArchiveModal = () =>
    openConfirmModal({
      type: "danger",
      title: "Archive task group",
      content: `Are you sure you want to archive this task group?`,
      confirmLabel: "Archive",
      confirmProps: { color: "red" },
      onConfirm: () => archiveForm.submit({ preserveScroll: true }),
    });

  const openRestoreModal = () =>
    openConfirmModal({
      type: "info",
      title: "Restore task group",
      content: `Are you sure you want to restore this task group?`,
      confirmLabel: "Restore",
      confirmProps: { color: "blue" },
      onConfirm: () => restoreForm.submit({ preserveScroll: true }),
    });

  const openEditModal = () => EditTasksGroupModal(group);

  return (
    <Group gap={0} justify="flex-end" {...props}>
      {((can("archive task group") && !route().params.archived) ||
        (can("restore task group") && route().params.archived) ||
        (can("edit task group") && !route().params.archived)) && (
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
              <IconDots style={{ width: rem(20), height: rem(20) }} stroke={1.5} />
            </ActionIcon>
          </Menu.Target>
          <Menu.Dropdown>
            {can("edit task group") && !route().params.archived && (
              <Menu.Item
                leftSection={
                  <IconPencil style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
                }
                onClick={openEditModal}
              >
                Edit
              </Menu.Item>
            )}
            {can("restore task group") && route().params.archived && (
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
            {can("archive task group") && !route().params.archived && (
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
