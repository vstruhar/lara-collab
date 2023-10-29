import { Button } from "@mantine/core";
import { IconChevronLeft } from "@tabler/icons-react";

export default function ActionButton({ children, ...props }) {
  return (
    <Button radius="xl" size="md" type="submit" {...props}>
      {children}
    </Button>
  );
}
