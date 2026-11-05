import { hasRoles } from "@/utils/user";
import { usePage } from "@inertiajs/react";
import { showNotification } from "@mantine/notifications";
import useNotificationsStore from "./store/useNotificationsStore";
import useTaskDrawerStore from "./store/useTaskDrawerStore";
import useTaskGroupsStore from "./store/useTaskGroupsStore";
import useTasksStore from "./store/useTasksStore";

export default function useWebSockets() {
  const { auth: { user } } = usePage().props;
  const { addNotification } = useNotificationsStore();
  const {
    addTaskLocally, updateTaskLocally, removeTaskLocally, restoreTaskLocally, addCommentLocally, addAttachmentsLocally,
    removeAttachmentLocally, addTimeLogLocally, removeTimeLogLocally, reorderTaskLocally, moveTaskLocally,
    replaceGroupTasksLocally,
  } = useTasksStore();
  const {
    addTaskGroupLocally, updateTaskGroupLocally, removeTaskGroupLocally, restoreTaskGroupLocally, reorderTaskGroupLocally,
  } = useTaskGroupsStore();

  const initUserWebSocket = () => {
    const channelName = `App.Models.User.${user.id}`;

    window.Echo.private(channelName).notification((notification) => {
      addNotification(notification);

      showNotification({
        title: notification.title,
        message: notification.subtitle,
        autoClose: 8000,
      });
    });

    return () => window.Echo.leave(channelName);
  };

  const handleCommentCreated = (event) => {
    const edit = useTaskDrawerStore.getState().edit;

    if (!edit.opened || Number(event.comment?.task_id) !== Number(edit.task?.id)) {
      return;
    }

    addCommentLocally(event.comment);
  };

  const handleTaskGroupRestored = (event) => {
    if (route().params?.archived) {
      removeTaskGroupLocally(event.taskGroup?.id);
      return;
    }

    restoreTaskGroupLocally(event.taskGroup);
    replaceGroupTasksLocally(event.taskGroup?.id, event.tasks);
  };

  const handleTaskHidden = (event) => {
    removeTaskLocally(event.taskId);

    const edit = useTaskDrawerStore.getState().edit;

    if (edit.opened && Number(edit.task?.id) === Number(event.taskId)) {
      useTaskDrawerStore.getState().closeEditTask();
    }
  };

  const listenToTaskEvents = (channel) => {
    channel
      .listen('Task\\TaskCreated', (event) => addTaskLocally(event.task))
      .listen('Task\\TaskUpdated', (event) => updateTaskLocally(event.taskId, event.property, event.value, event.relatedData))
      .listen('Task\\TaskRestored', (event) => restoreTaskLocally(event.groupId, event.task))
      .listen('Task\\AttachmentsUploaded', (event) => addAttachmentsLocally(event.attachments))
      .listen('Task\\AttachmentDeleted', (event) => removeAttachmentLocally(event.taskId, event.attachmentId))
      .listen('Task\\TimeLogCreated', (event) => addTimeLogLocally(event.timeLog))
      .listen('Task\\TimeLogDeleted', (event) => removeTimeLogLocally(event.taskId, event.timeLogId))
      .listen('Task\\CommentCreated', handleCommentCreated);
  };

  const initProjectWebSocket = (project) => {
    const projectChannel = window.Echo.private(`App.Models.Project.${project.id}`);
    const isClient = hasRoles(user, ['client']);

    listenToTaskEvents(projectChannel);

    projectChannel
      .listen('Task\\TaskDeleted', (event) => removeTaskLocally(event.taskId))
      .listen('Task\\TaskOrderChanged', (event) => reorderTaskLocally(event.groupId, event.taskIds))
      .listen('Task\\TaskGroupChanged', (event) => moveTaskLocally(event.fromGroupId, event.toGroupId, event.taskIds, event.taskId))
      .listen('TaskGroup\\TaskGroupCreated', (event) => addTaskGroupLocally(event.taskGroup))
      .listen('TaskGroup\\TaskGroupUpdated', (event) => updateTaskGroupLocally(event.taskGroup))
      .listen('TaskGroup\\TaskGroupDeleted', (event) => removeTaskGroupLocally(event.taskGroupId))
      .listen('TaskGroup\\TaskGroupOrderChanged', (event) => reorderTaskGroupLocally(event.taskGroupIds));

    if (isClient) {
      projectChannel.listen('Task\\TaskVisibilityChanged', (event) => {
        if (event.hidden) {
          handleTaskHidden(event);
          return;
        }

        addTaskLocally(event.task);
      });
      projectChannel.listen('TaskGroup\\TaskGroupRestored', handleTaskGroupRestored);
    } else {
      const internalChannel = window.Echo.private(`App.Models.Project.${project.id}.internal`);

      listenToTaskEvents(internalChannel);
      internalChannel
        .listen('Task\\TaskDeleted', (event) => removeTaskLocally(event.taskId))
        .listen('TaskGroup\\TaskGroupRestored', handleTaskGroupRestored);
    }

    return () => {
      window.Echo.leave(`App.Models.Project.${project.id}`);

      if (!isClient) {
        window.Echo.leave(`App.Models.Project.${project.id}.internal`);
      }
    };
  };

  return { initUserWebSocket, initProjectWebSocket };
}
