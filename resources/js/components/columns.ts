import { h } from 'vue'
import { type Payment } from '@/types';
import type { ColumnDef } from '@tanstack/vue-table';
import DropdownAction from '@/components/DataTableDropDown.vue'

export const columns: ColumnDef<Payment>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: ({ row }) => h('div', row.getValue('id'))
    },
    {
        accessorKey: 'amount',
        header: () => h('div', { class: 'text-right' }, 'Amount'),
        cell: ({ row }) => {
            const amount = Number.parseFloat(row.getValue('amount'))
            const formatted = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            }).format(amount)
            return h('div', { class: 'text-right font-medium' }, formatted)
        },
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({ row }) => h('div', row.getValue('status'))
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: ({ row }) => h('div', row.getValue('email'))
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
          const payment = row.original
    
          return h('div', { class: 'relative' }, h(DropdownAction, {
            payment,
          }))
        },
      },
]