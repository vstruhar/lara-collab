import { usePage } from "@inertiajs/react";
import { showNotification } from "@mantine/notifications";
import axios from "axios";

const useWebSocketsNotifications = () => {
  const { auth: { user } } = usePage().props;

  const init = () => {
    window.Echo.private(`App.Models.User.${user.id}`).notification((notification) => {
      showNotification({
        title: 'New task',
        message: `"${notification.payload.name}" was added by ${notification.payload.createdBy}`,
        autoClose: 6000,
      });

      axios.put(route('notifications.read', notification.id))
        .catch(() => console.warn('Failed to set notification as seen'));
    });
  };

  return { init };
}

export default useWebSocketsNotifications;
