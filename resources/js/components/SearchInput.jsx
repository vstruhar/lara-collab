import { reloadWithoutQueryParams } from "@/utils/route";
import { TextInput, rem } from "@mantine/core";
import { useDebouncedValue, useDidUpdate } from "@mantine/hooks";
import { IconSearch, IconX } from "@tabler/icons-react";
import { useState } from "react";

export default function SearchInput({ search, ...props }) {
  const [value, setValue] = useState(route().params.search || "");
  const [debounced] = useDebouncedValue(value, 250);

  useDidUpdate(() => {
    if (debounced !== "") search(debounced);
    else reloadWithoutQueryParams({ exclude: ["search"] });
  }, [debounced]);

  return (
    <TextInput
      value={value}
      onChange={(event) => setValue(event.currentTarget.value)}
      radius="xl"
      leftSectionWidth={42}
      leftSection={
        <IconSearch style={{ width: rem(16), height: rem(16) }} stroke={2.5} />
      }
      rightSectionWidth={38}
      rightSection={
        debounced !== "" && (
          <IconX
            style={{
              width: rem(16),
              height: rem(16),
              cursor: "pointer",
            }}
            stroke={2.5}
            onClick={() => setValue("")}
          />
        )
      }
      {...props}
    />
  );
}
