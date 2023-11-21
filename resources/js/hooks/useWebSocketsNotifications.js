import { usePage } from "@inertiajs/react";
import axios from "axios";

export default function useWebSocketsNotifications() {
  const { auth: { user } } = usePage().props;

  const init = () => {
    window.Echo.private(`App.Models.User.${user.id}`).notification((notification) => {
      console.log('Notification received', notification);

      // showNotification({
      //   title: 'New task',
      //   message: `"${notification.payload.name}" was added by ${notification.payload.created_by}`,
      //   autoClose: 6000,
      // });

      axios.put(route('notifications.read', notification.id))
        .catch(() => console.warn('Failed to set notification as seen'));
    });
  };

  return { init };
}
