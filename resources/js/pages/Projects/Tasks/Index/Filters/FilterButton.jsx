import { Button, rem } from "@mantine/core";
import { IconCheck } from "@tabler/icons-react";

export default function FilterButton({ selected, children, ...props }) {
  return (
    <Button
      variant={selected ? "filled" : "default"}
      opacity={selected ? 1 : 0.6}
      radius="md"
      justify="flex-start"
      size="xs"
      fullWidth
      leftSection={
        <IconCheck
          stroke={2}
          opacity={selected ? 1 : 0.2}
          radius="xl"
          style={{
            width: rem(13),
            height: rem(13),
            marginLeft: rem(3),
            marginRight: rem(3),
          }}
        />
      }
      style={{ border: "none", height: rem(32) }}
      {...props}
    >
      {children}
    </Button>
  );
}
