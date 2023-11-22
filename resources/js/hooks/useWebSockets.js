import { usePage } from "@inertiajs/react";
import { showNotification } from "@mantine/notifications";
import useNotificationsStore from "./store/useNotificationsStore";
import useTasksStore from "./store/useTasksStore";

export default function useWebSockets() {
  const { auth: { user } } = usePage().props;
  const {addNotification} = useNotificationsStore();
  const {findTask, updateTaskLocally, archiveTaskLocally, restoreTaskLocally} = useTasksStore();

  const initUserWebSocket = () => {
    window.Echo.private(`App.Models.User.${user.id}`).notification((notification) => {
      console.log('Notification received', notification);

      addNotification(notification);

      showNotification({
        title: notification.title,
        message: notification.subtitle,
        autoClose: 8000,
      });
    });
  };

  const initProjectWebSocket = (project) => {
    window.Echo.private(`App.Models.Project.${project.id}`)
      .listen('Task\\TaskUpdated', (event) => {
        updateTaskLocally(findTask(event.task.id), event.task);
      })
      .listen('Task\\TaskDeleted', (event) => {
        archiveTaskLocally(
          findTask(event.taskId)
        );
      })
      .listen('Task\\TaskRestored', (event) => {
        restoreTaskLocally(event.groupId, event.task);
      });
  };

  return {initUserWebSocket, initProjectWebSocket};
}
