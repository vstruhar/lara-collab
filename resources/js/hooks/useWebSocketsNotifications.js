import { getMessage } from '@/utils/notification';
import { usePage } from "@inertiajs/react";
import { showNotification } from "@mantine/notifications";
import useNotificationsStore from "./store/useNotificationsStore";

export default function useWebSocketsNotifications() {
  const { auth: { user } } = usePage().props;
  const {addNotification} = useNotificationsStore();

  const init = () => {
    window.Echo.private(`App.Models.User.${user.id}`).notification((notification) => {
      console.log('Notification received', notification);

      addNotification(notification);

      showNotification({
        ...getMessage(notification),
        autoClose: 8000,
      });
    });
  };

  return { init };
}
