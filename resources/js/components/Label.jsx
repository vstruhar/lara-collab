import { ColorSwatch, Group, Text } from "@mantine/core";

export function Label({ name, color, size = 10, dot = true }) {
  return (
    <Group gap={5} my={2} wrap="nowrap">
      {dot === true && <ColorSwatch color={color} size={size} />}
      <Text fz={size} tt="uppercase" c={color} fw={500}>
        {name}
      </Text>
    </Group>
  );
}
