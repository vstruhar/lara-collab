import { ColorSwatch, Group, Text } from "@mantine/core";

export function Label({ name, color, size = 10 }) {
  return (
    <Group gap={5} my={2}>
      <ColorSwatch color={color} size={size} />
      <Text fz={size} tt="uppercase" c={color} fw={500}>
        {name}
      </Text>
    </Group>
  );
}
