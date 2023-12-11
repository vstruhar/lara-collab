import { router } from "@inertiajs/react";
import { Menu, Pill, rem } from "@mantine/core";
import { IconCheck, IconMail, IconPlus } from "@tabler/icons-react";

export default function StatusDropdown({ invoiceId, status }) {
  const setStatus = (id, status) => {
    router.put(route("invoices.status", id), { status });
  };

  const color = (status) => {
    if (status === "new") return "blue";
    if (status === "sent") return "yellow";
    if (status === "paid") return "green";
  };

  return (
    <Menu withArrow shadow="md" width={120} disabled={!can("change invoice status")}>
      <Menu.Target>
        <Pill
          size="sm"
          fw={600}
          variant="default"
          bg={color(status)}
          c="white"
          styles={{
            label: { cursor: can("change invoice status") ? "pointer" : "default" },
            root: { cursor: "pointer" },
          }}
        >
          {status}
        </Pill>
      </Menu.Target>

      <Menu.Dropdown>
        <Menu.Item
          leftSection={<IconPlus style={{ width: rem(14), height: rem(14) }} />}
          onClick={() => setStatus(invoiceId, "new")}
        >
          New
        </Menu.Item>
        <Menu.Item
          my={5}
          leftSection={<IconMail style={{ width: rem(14), height: rem(14) }} />}
          onClick={() => setStatus(invoiceId, "sent")}
        >
          Sent
        </Menu.Item>
        <Menu.Item
          leftSection={<IconCheck style={{ width: rem(14), height: rem(14) }} />}
          onClick={() => setStatus(invoiceId, "paid")}
        >
          Paid
        </Menu.Item>
      </Menu.Dropdown>
    </Menu>
  );
}
