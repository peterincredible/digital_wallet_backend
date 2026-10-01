# Digital Wallet []
<p> here is the link to the [image cant be seen](http://digitalbuck23.s3-website-us-east-1.amazonaws.com/)
<p>This is a minified digital wallet which shows the core functionality or i can say a proof of concept  of how a digital wallet works</p>
# Basic Usage
<p> once the person gets on the platform he/she will be redirected to the signup Page if not authenticated, where they will be forced to login or Register</p>
<p>once users are done authenticated they see there dashboard and there various wallets(ngn,usd,usdt)</p>
<p> and users will see the routes to add funds (Top up) or send Funds(Wallet 2 Wallet ) </p>
<p> and again users will be able to see there transactions on who they sent to with amount or who sent to them with amount</p>

# Basic Engineering Descisions i took
1. when adding funds i made it atomic by appllying transactions to it
2. since each users has a fixed wallet (3) then i created a **wallet table** that house the three wallets**(ngn,usd,usdt)* there is not need of creating relationship 
3. when transfering funds from one person to another i made sure i lock both the rows of the sender and the reciever for strong consistency i wanted to change the isolation level to read committed but just changed my mind
4. i used the email as the means of sending funds between (w2w) becuase the user email must be unique
5. the **transaction table** is either in pending or completed state and hold a status that shows the transanction type(either Topup or w2w) while the **transaction detail** holds the transaction flow(debit or credit account) 
6. the transaction is first initiated and the id is used as a tool to enforce *idempotency* to prevent double transfer or anything that will bring the financial records in an inconsistent state
7. allot of edge cases was handled like restricting the user of transfering funds to he/her selfs, or sending to unknown/unregistered user also cases of processing already completed transactions is blocked
8. still ... working on it more updated ... will be added b4 the deadline

