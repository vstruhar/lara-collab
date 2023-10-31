import { Button } from "@mantine/core";

export default function ActionButton({ children, ...props }) {
  return (
    <Button size="md" type="submit" {...props}>
      {children}
    </Button>
  );
}
