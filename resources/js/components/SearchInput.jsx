import { TextInput, rem } from "@mantine/core";
import { useDebouncedValue } from "@mantine/hooks";
import { IconSearch } from "@tabler/icons-react";
import { useEffect, useState } from "react";

export default function SearchInput({ search, ...props }) {
  const [value, setValue] = useState("");
  const [debounced] = useDebouncedValue(value, 250);

  useEffect(() => search(debounced), [debounced]);

  return (
    <TextInput
      value={value}
      onChange={(event) => setValue(event.currentTarget.value)}
      radius="xl"
      leftSectionWidth={42}
      leftSection={
        <IconSearch style={{ width: rem(16), height: rem(16) }} stroke={2.5} />
      }
      {...props}
    />
  );
}
