import useTaskDrawerStore from "@/hooks/store/useTaskDrawerStore";
import { Drawer } from "@mantine/core";

export function CreateTaskDrawer() {
  const { create, closeCreateTask } = useTaskDrawerStore();

  return (
    <Drawer
      opened={create.opened}
      onClose={closeCreateTask}
      position="right"
      size={1000}
      overlayProps={{ backgroundOpacity: 0.55, blur: 3 }}
    >
      CONTENT {create.group_id}
    </Drawer>
  );
}
