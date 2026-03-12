export type Wallet = {
    id: string;
    balance: number;
    formatted_balance: string;
};

export type Transaction = {
    id: string;
    type: 'deposit' | 'transfer_in' | 'transfer_out';
    status: 'completed' | 'reversed' | 'failed';
    amount: number;
    formatted_amount: string;
    balance_before: number;
    balance_after: number;
    formatted_balance_before: string;
    formatted_balance_after: string;
    counterpart_name?: string;
    counterpart_email?: string;
    description?: string;
    reversed_at?: string;
    created_at: string;
};

export type TransactionPaginated = {
    data: Transaction[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};
